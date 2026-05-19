<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\AuditLog;

class LoginController extends Controller
{
    /**
     * Maksimum percobaan login sebelum di-throttle
     */
    protected int $maxAttempts = 5;

    /**
     * Durasi lockout dalam detik (2 menit)
     */
    protected int $decaySeconds = 120;

    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // ── Rate Limiting / Throttle ──
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            // Log percobaan brute force
            AuditLog::create([
                'user_id' => null,
                'action'  => 'LOGIN_BLOCKED',
                'entity'  => 'AUTH',
                'desc'    => "Login diblokir untuk {$request->email} – terlalu banyak percobaan. Tunggu {$seconds} detik.",
            ]);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Reset rate limiter on success
            RateLimiter::clear($throttleKey);
            
            $request->session()->regenerate();

            $user = Auth::user();

            AuditLog::create([
                'user_id' => $user->id,
                'action'  => 'LOGIN',
                'entity'  => 'AUTH',
                'desc'    => "Pengguna {$user->nama} berhasil login.",
            ]);

            return $this->redirectUserByRole($user);
        }

        // Increment rate limiter on failure
        RateLimiter::hit($throttleKey, $this->decaySeconds);
        $attemptsLeft = RateLimiter::retriesLeft($throttleKey, $this->maxAttempts);

        $message = 'Email atau password salah.';
        if ($attemptsLeft <= 2 && $attemptsLeft > 0) {
            $message .= " Sisa percobaan: {$attemptsLeft}x.";
        }

        return back()->withErrors([
            'email' => $message,
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action'  => 'LOGOUT',
                'entity'  => 'AUTH',
                'desc'    => "Pengguna {$user->nama} logout.",
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    protected function redirectUserByRole($user)
    {
        return match($user->role) {
            'admin'    => redirect()->intended('admin/dashboard'),
            'hrd'      => redirect()->intended('hrd/dashboard'),
            'karyawan' => redirect()->intended('karyawan/dashboard'),
            default    => redirect('/'),
        };
    }

    /**
     * Generate throttle key berdasarkan email + IP
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(
            Str::lower($request->input('email')) . '|' . $request->ip()
        );
    }
}
