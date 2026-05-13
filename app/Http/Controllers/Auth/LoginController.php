<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class LoginController extends Controller
{
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
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            
            // Log Aktivitas
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'LOGIN',
                'entity' => 'AUTH',
                'desc' => "Pengguna {$user->nama} berhasil login.",
            ]);

            return $this->redirectUserByRole($user);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'LOGOUT',
                'entity' => 'AUTH',
                'desc' => "Pengguna {$user->nama} logout.",
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
            'admin' => redirect()->intended('admin/dashboard'),
            'hrd' => redirect()->intended('hrd/dashboard'),
            'karyawan' => redirect()->intended('karyawan/dashboard'),
            default => redirect('/'),
        };
    }
}
