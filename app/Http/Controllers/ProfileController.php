<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    // ---------------------------------------------------------------
    // SHOW SETTINGS PAGE
    // ---------------------------------------------------------------

    public function edit()
    {
        return view('settings', [
            'user' => Auth::user(),
        ]);
    }

    // ---------------------------------------------------------------
    // UPDATE PROFILE INFO & AVATAR
    // ---------------------------------------------------------------

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'password'   => ['nullable', 'confirmed', Password::min(8)],
        ]);

        // Only require current password if changing email or password
        if ($request->filled('password') || $data['email'] !== $user->email) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
            ], [
                'current_password.current_password' => 'Your current password is incorrect.',
            ]);
        }

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extension = $file->getClientOriginalExtension();
            
            // Clean filename without folder duplication
            $fileName = time() . '_' . $user->id . '.' . $extension;

            $supabaseUrl = env('SUPABASE_URL', 'https://ipqhovyvflixeealsidh.supabase.co');
            $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY', env('SUPABASE_KEY'));

            // Delete existing local file if replacing a previous local avatar
            if ($user->avatar && !str_starts_with($user->avatar, 'http') && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            if ($supabaseKey) {
                // Prepare request with required Supabase API headers
                $httpClient = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'apiKey'        => $supabaseKey,
                    'Content-Type'  => $file->getMimeType(),
                ]);

                // Disable SSL check ONLY on local dev machine to bypass cURL error 60.
                // Enforces strict SSL verification when deployed in production.
                if (app()->environment('local')) {
                    $httpClient->withoutVerifying();
                }

                $response = $httpClient->withBody(
                    file_get_contents($file->getRealPath()),
                    $file->getMimeType()
                )->post("{$supabaseUrl}/storage/v1/object/avatars/{$fileName}");

                if ($response->successful() || $response->status() === 200) {
                    $user->avatar = "{$supabaseUrl}/storage/v1/object/public/avatars/{$fileName}";
                } else {
                    // Fallback to local storage if API fails
                    $user->avatar = $file->store('avatars', 'public');
                }
            } else {
                // Local disk storage fallback
                $user->avatar = $file->store('avatars', 'public');
            }
        }

        $user->first_name = $data['first_name'];
        $user->last_name  = $data['last_name'];
        $user->name       = $data['first_name'] . ' ' . $data['last_name'];
        $user->email      = $data['email'];

        if ($request->filled('password')) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'Your profile has been updated.');
    }

    // ---------------------------------------------------------------
    // DELETE ACCOUNT
    // ---------------------------------------------------------------

    public function destroy(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ], [
            'current_password.current_password' => 'Your password is incorrect.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Remove avatar file on account deletion if local
        if ($user->avatar && !str_starts_with($user->avatar, 'http') && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Your account has been permanently deleted.');
    }
}