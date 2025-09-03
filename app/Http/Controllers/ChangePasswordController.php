<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ChangePasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', Password::min(8)],
                'new_password_confirmation' => ['required', 'string', 'same:new_password'],
            ], [
                'current_password.required' => 'Password saat ini harus diisi.',
                'new_password.required' => 'Password baru harus diisi.',
                'new_password.min' => 'Password baru minimal 8 karakter.',
                'new_password_confirmation.required' => 'Konfirmasi password harus diisi.',
                'new_password_confirmation.same' => 'Konfirmasi password tidak cocok dengan password baru.',
            ]);

            $user = Auth::user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Password saat ini tidak benar.'
                ]);
            }

            // Check if new password is different from current password
            if (Hash::check($request->new_password, $user->password)) {
                return back()->withErrors([
                    'new_password' => 'Password baru harus berbeda dari password saat ini.'
                ]);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            // Log the password change activity
            Log::info('Password changed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);

            // Logout user to force re-login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Password berhasil diubah! Silakan login dengan password baru.');
        } catch (\Exception $e) {
            Log::error('Password change failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors([
                'general' => 'Terjadi kesalahan saat mengubah password. Silakan coba lagi.'
            ]);
        }
    }
}