<?php
// FILE: app/Http/Controllers/Auth/SocialiteController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    protected function googleDriver()
    {
        $redirectUrl = config('services.google.redirect');

        if (empty($redirectUrl)) {
            $redirectUrl = url('/auth/google/callback');
            if (!app()->environment('local') && str_starts_with($redirectUrl, 'http://')) {
                $redirectUrl = 'https://' . substr($redirectUrl, 7);
            }
        }

        $driver = Socialite::driver('google')->redirectUrl($redirectUrl);

        if (app()->environment('local')) {
            $driver->setHttpClient(new Client(['verify' => false]));
        }

        return $driver;
    }

    public function redirectToGoogle()
    {
        return $this->googleDriver()->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = $this->googleDriver()->user();
        } catch (\Throwable $e) {
            Log::error('Google OAuth Callback Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Google sign-in failed: ' . $e->getMessage());
        }

        $email = $googleUser->getEmail();
        $googleId = $googleUser->getId();
        $hasGoogleIdCol = Schema::hasColumn('users', 'google_id');

        // 1. Returning user -> log directly into the appropriate dashboard
        $existingUser = User::where('email', $email)
            ->when($hasGoogleIdCol, fn($q) => $q->orWhere('google_id', $googleId))
            ->first();

        if ($existingUser) {
            if ($hasGoogleIdCol && empty($existingUser->google_id)) {
                $existingUser->google_id = $googleId;
                $existingUser->save();
            }

            Auth::login($existingUser, true);
            $request->session()->regenerate();

            // Redirect admins directly to the Admin Dashboard
            if ($existingUser->role === 'admin' || (method_exists($existingUser, 'isAdmin') && $existingUser->isAdmin())) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->intended(route('dashboard'));
        }

        // 2. Extract First & Last Name from Google profile for form autofill
        $rawUser = $googleUser->user ?? [];
        $firstName = $rawUser['given_name'] ?? '';
        $lastName  = $rawUser['family_name'] ?? '';

        if (empty($firstName)) {
            $parts = explode(' ', trim($googleUser->getName() ?? ''), 2);
            $firstName = $parts[0] ?? '';
            $lastName  = $parts[1] ?? '';
        }

        // 3. Store Google info in session and send user to profile completion form
        $request->session()->put('google_signup_data', [
            'google_id'  => $googleId,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'avatar'     => $googleUser->getAvatar(),
        ]);

        return redirect()->route('auth.google.complete');
    }

    public function showCompleteForm(Request $request)
    {
        $googleData = $request->session()->get('google_signup_data');

        if (!$googleData || empty($googleData['email'])) {
            return redirect()->route('register')->with('error', 'Please start by signing in with Google first.');
        }

        return view('auth.google-complete', compact('googleData'));
    }

    public function completeRegistration(Request $request)
    {
        $googleData = $request->session()->get('google_signup_data');

        if (!$googleData || empty($googleData['email'])) {
            return redirect()->route('register')->with('error', 'Your Google sign-up session expired. Please try again.');
        }

        $request->validate([
            'role'       => 'required|in:student,teacher',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
            'avatar'     => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $avatarUrl = $googleData['avatar'] ?? null;

        if ($request->hasFile('avatar')) {
            $avatarUrl = $this->uploadAvatar($request->file('avatar'));
        }

        $user = new User();

        if (Schema::hasColumn('users', 'name')) {
            $user->name = trim($request->first_name . ' ' . $request->last_name);
        }
        if (Schema::hasColumn('users', 'first_name')) {
            $user->first_name = $request->first_name;
        }
        if (Schema::hasColumn('users', 'last_name')) {
            $user->last_name = $request->last_name;
        }

        $user->email    = $googleData['email'];
        $user->password = Hash::make($request->password);
        $user->role     = $request->role;

        if (Schema::hasColumn('users', 'avatar_url')) {
            $user->avatar_url = $avatarUrl;
        }
        if (Schema::hasColumn('users', 'google_id')) {
            $user->google_id = $googleData['google_id'];
        }
        if (Schema::hasColumn('users', 'email_verified_at')) {
            $user->email_verified_at = now();
        }

        $user->save();

        $request->session()->forget('google_signup_data');
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    protected function uploadAvatar($file): ?string
    {
        $supabaseUrl = rtrim((string) config('services.supabase.url', env('SUPABASE_URL', '')), '/');
        $serviceKey  = (string) config('services.supabase.service_role_key', env('SUPABASE_SERVICE_ROLE_KEY', ''));

        if ($supabaseUrl !== '' && $serviceKey !== '') {
            $fileName = 'avatar_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $uploadUrl = "{$supabaseUrl}/storage/v1/object/avatars/{$fileName}";

            $http = Http::withHeaders([
                'Authorization' => 'Bearer ' . $serviceKey,
                'apikey'        => $serviceKey,
                'Content-Type'  => $file->getMimeType(),
            ]);

            if (app()->environment('local')) {
                $http->withoutVerifying();
            }

            $response = $http->withBody(file_get_contents($file->getRealPath()), $file->getMimeType())->post($uploadUrl);

            if ($response->successful()) {
                return "{$supabaseUrl}/storage/v1/object/public/avatars/{$fileName}";
            }
        }

        $path = $file->store('avatars', 'public');
        return Storage::url($path);
    }
}