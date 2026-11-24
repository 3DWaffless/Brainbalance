<?php
// FILE: app/Http/Controllers/Auth/SocialiteController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialiteController extends Controller
{
    protected function googleDriver()
    {
        $redirectUrl = trim((string) config('services.google.redirect'), " \t\n\r\0\x0B\"'");

        if (
            !app()->environment('local')
            && (empty($redirectUrl) || str_contains($redirectUrl, '127.0.0.1') || str_contains($redirectUrl, 'localhost'))
        ) {
            $redirectUrl = url('/auth/google/callback');
        }

        if (empty($redirectUrl)) {
            $redirectUrl = url('/auth/google/callback');
        }

        if (!app()->environment('local') && str_starts_with($redirectUrl, 'http://')) {
            $redirectUrl = 'https://' . substr($redirectUrl, 7);
        }

        return Socialite::driver('google')->redirectUrl($redirectUrl);
    }

    public function redirectToGoogle()
    {
        return $this->googleDriver()->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = $this->googleDriver()->user();
        } catch (InvalidStateException $e) {
            Log::error('Google OAuth InvalidStateException: session cookie missing or mismatched.');
            return redirect()->route('login')->with('error', 'Google sign-in session expired. Please try again.');
        } catch (\Throwable $e) {
            Log::error('Google OAuth Callback Error [' . get_class($e) . ']: ' . $e->getMessage());
            $detail = $e->getMessage() ?: class_basename($e);
            return redirect()->route('login')->with('error', 'Google sign-in failed: ' . $detail);
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

        $encryptedGoogleData = Crypt::encryptString(json_encode($googleData));

        return view('auth.google-complete', compact('googleData', 'encryptedGoogleData'));
    }

    public function completeRegistration(Request $request)
    {
        $googleData = $request->session()->get('google_signup_data');

        if ((!$googleData || empty($googleData['email'])) && $request->filled('google_payload')) {
            try {
                $decoded = json_decode(Crypt::decryptString((string) $request->input('google_payload')), true);
                if (is_array($decoded) && !empty($decoded['email']) && !empty($decoded['google_id'])) {
                    $googleData = $decoded;
                    $request->session()->put('google_signup_data', $googleData);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to decrypt fallback google_payload: ' . $e->getMessage());
            }
        }

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
            $uploadedUrl = $this->uploadAvatar($request->file('avatar'));
            if (!empty($uploadedUrl)) {
                $avatarUrl = $uploadedUrl;
            }
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
        if (Schema::hasColumn('users', 'avatar')) {
            $user->avatar = $avatarUrl;
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
        $supabaseUrl = rtrim(trim((string) (config('services.supabase.url') ?: env('SUPABASE_URL', '')), " \t\n\r\0\x0B\"'"), '/');
        $serviceKey  = trim((string) (config('services.supabase.service_role_key') ?: env('SUPABASE_SERVICE_ROLE_KEY', '')), " \t\n\r\0\x0B\"'");
        $bucket      = trim((string) (config('filesystems.disks.s3.bucket') ?: config('services.supabase.bucket') ?: env('AWS_BUCKET') ?: 'brainbalance-files'), " \t\n\r\0\x0B\"'/");

        if ($bucket === '') {
            $bucket = 'brainbalance-files';
        }

        if ($supabaseUrl !== '' && $serviceKey !== '') {
            $extension  = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $fileName   = 'avatar_' . Str::uuid() . '.' . $extension;
            $objectPath = "avatars/{$fileName}";
            $uploadUrl  = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$objectPath}";

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $serviceKey,
                    'apikey'        => $serviceKey,
                    'Content-Type'  => $file->getMimeType() ?: 'application/octet-stream',
                    'x-upsert'      => 'true',
                ])->withBody(file_get_contents($file->getRealPath()), $file->getMimeType() ?: 'application/octet-stream')
                  ->post($uploadUrl);

                if ($response->successful()) {
                    return "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$objectPath}";
                }

                Log::error('Supabase Google signup avatar upload failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'url'    => $uploadUrl,
                ]);
            } catch (\Throwable $e) {
                Log::error('Supabase Google signup avatar upload exception: ' . $e->getMessage());
            }
        } else {
            Log::warning('Supabase credentials missing during Google signup avatar upload.');
        }

        $path = $file->store('avatars', 'public');
        return Storage::url($path);
    }
}