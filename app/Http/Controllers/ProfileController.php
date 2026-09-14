<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
    // UPDATE PROFILE INFO
    // ---------------------------------------------------------------

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'   => ['nullable', 'confirmed', Password::min(8)],
        ]);

        // Only require current password if the user is actually changing something sensitive
        if ($request->filled('password') || $data['email'] !== $user->email) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
            ], [
                'current_password.current_password' => 'Your current password is incorrect.',
            ]);
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

        $user = Auth::user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Your account has been permanently deleted.');
    }
}
