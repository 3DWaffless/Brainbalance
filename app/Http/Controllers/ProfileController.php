<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    protected function prepareSupabaseDisk(): string
    {
        $key    = config('filesystems.disks.supabase.key') ?: (getenv('SUPABASE_S3_KEY') ?: null);
        $secret = config('filesystems.disks.supabase.secret') ?: (getenv('SUPABASE_S3_SECRET') ?: null);
        $region = config('filesystems.disks.supabase.region') ?: (getenv('SUPABASE_S3_REGION') ?: 'ap-northeast-2');
        $bucket = config('filesystems.disks.supabase.bucket') ?: (getenv('SUPABASE_S3_BUCKET') ?: 'brainbalance-files');

        if (!$key && !app()->environment('production')) {
            return 'public';
        }

        $endpoint = (string) config('filesystems.disks.supabase.endpoint', '');
        if ($endpoint === '' || str_contains($endpoint, 'your-project-ref')) {
            $liveEnv = (string) (getenv('SUPABASE_S3_ENDPOINT') ?: ($_SERVER['SUPABASE_S3_ENDPOINT'] ?? ''));
            if ($liveEnv !== '' && !str_contains($liveEnv, 'your-project-ref')) {
                $endpoint = $liveEnv;
            } else {
                $pgSources = [
                    (string) config('database.connections.pgsql.host', ''),
                    (string) config('database.connections.pgsql.username', ''),
                    (string) config('database.connections.pgsql.url', ''),
                ];

                $projectRef = 'ipqhovyvflixeealsidh';
                foreach ($pgSources as $src) {
                    if (preg_match('/(?:db\.|postgres\.)([a-z0-9]{15,})/i', $src, $m)) {
                        $projectRef = $m[1];
                        break;
                    }
                }

                $endpoint = 'https://' . $projectRef . '.supabase.co/storage/v1/s3';
            }
        }

        config([
            'filesystems.disks.supabase.key'      => $key,
            'filesystems.disks.supabase.secret'   => $secret,
            'filesystems.disks.supabase.region'   => $region,
            'filesystems.disks.supabase.bucket'   => $bucket,
            'filesystems.disks.supabase.endpoint' => rtrim($endpoint, '/'),
        ]);

        Storage::forgetDisk('supabase');

        return 'supabase';
    }

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
            'last_name'  => ['nullable', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
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

        // Handle Avatar Upload to Supabase Storage (brainbalance-files/avatars/)
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
            $fileName = time() . '_' . $user->id . '.' . $extension;

            try {
                $disk = $this->prepareSupabaseDisk();

                // Clean up previous avatar file if present
                if ($user->avatar) {
                    if (!str_starts_with($user->avatar, 'http') && Storage::disk('public')->exists($user->avatar)) {
                        Storage::disk('public')->delete($user->avatar);
                    } elseif ($disk === 'supabase' && preg_match('#/storage/v1/object/public/[^/]+/(avatars/.+)$#', $user->avatar, $m)) {
                        try {
                            Storage::disk('supabase')->delete($m[1]);
                        } catch (\Throwable $e) {
                            // Ignore cleanup errors for old avatar
                        }
                    }
                }

                $storedPath = $file->storeAs('avatars', $fileName, $disk);
                if (!$storedPath) {
                    throw new \RuntimeException('Storage returned an empty path.');
                }

                if ($disk === 'supabase') {
                    $endpoint   = rtrim((string) config('filesystems.disks.supabase.endpoint'), '/');
                    $bucket     = trim((string) config('filesystems.disks.supabase.bucket'), '/');
                    $publicBase = preg_replace('#/storage/v1/s3$#', '/storage/v1/object/public', $endpoint);
                    $user->avatar = $publicBase . '/' . $bucket . '/' . ltrim($storedPath, '/');
                } else {
                    $user->avatar = $storedPath;
                }
            } catch (\Throwable $e) {
                $reason = $e->getPrevious()?->getMessage() ?? $e->getMessage();
                Log::error('Avatar upload failed: ' . $reason, ['exception' => $e]);

                return back()
                    ->withErrors(['avatar' => 'Profile picture upload failed: ' . $reason])
                    ->withInput();
            }
        }

        $user->first_name = $data['first_name'];
        $user->last_name  = $data['last_name'] ?? '';
        $user->name       = trim($data['first_name'] . ' ' . ($data['last_name'] ?? ''));
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