<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Handle the Google callback.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                // Update Google ID if not exists
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                    ]);
                }

                // Update avatar if changed
                if ($googleUser->getAvatar() && $user->avatar !== $googleUser->getAvatar()) {
                    $user->update([
                        'avatar' => $googleUser->getAvatar(),
                    ]);
                }
            } else {
                // Check if email domain is allowed (optional)
                $allowedDomains = config('services.google.allowed_domains', []);
                if (!empty($allowedDomains) && !in_array(explode('@', $googleUser->getEmail())[1], $allowedDomains)) {
                    return redirect()->route('login')
                        ->withErrors(['email' => 'Domain email tidak diizinkan untuk akses portal.']);
                }

                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                    'role' => 'teacher', // Default role, can be overridden by admin
                ]);
            }

            // Log the authentication
            AuditLog::create([
                'user_id' => $user->id,
                'resource' => 'auth',
                'action' => 'login',
                'resource_id' => $user->id,
                'details' => [
                    'method' => 'google_oauth',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Auth::login($user);

            $request->session()->regenerate();

            // Redirect based on user role
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                return redirect()->intended(route('teacher.dashboard'));
            }

        } catch (\Exception $e) {
            \Log::error('Google authentication error: ' . $e->getMessage());

            return redirect()->route('login')
                ->withErrors(['google' => 'Terjadi kesalahan saat login dengan Google. Silakan coba lagi.']);
        }
    }

    /**
     * Logout the user and invalidate the session.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Log the logout
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'resource' => 'auth',
                'action' => 'logout',
                'resource_id' => $user->id,
                'details' => [
                    'method' => 'manual',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}