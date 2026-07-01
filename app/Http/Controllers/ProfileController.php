<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $divisions = Divisi::all();

        return view('profile.index', compact('user', 'divisions'));
    }

    /**
     * Update profile information (name & email only).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh pengguna lain.',
        ]);

        $user->nama = $validated['nama'];
        $user->email = $validated['email'];
        $user->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'UPDATE_PROFILE',
            'entity' => 'User',
            'desc' => 'Memperbarui informasi profil (nama & email)',
        ]);

        return redirect()->back()->with('success', 'Informasi profil berhasil diperbarui.');
    }

    /**
     * Handle password change with current password verification.
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // Verify current password
        if (! Hash::check($validated['current_password'], $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Kata sandi saat ini tidak sesuai.'])
                ->with('error', 'Kata sandi lama yang Anda masukkan salah.');
        }

        // Prevent using the same password
        if (Hash::check($validated['password'], $user->password)) {
            return redirect()->back()
                ->withErrors(['password' => 'Kata sandi baru tidak boleh sama dengan kata sandi lama.'])
                ->with('error', 'Kata sandi baru harus berbeda dari kata sandi lama.');
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'CHANGE_PASSWORD',
            'entity' => 'User',
            'desc' => 'Mengubah kata sandi akun',
        ]);

        return redirect()->back()->with('success', 'Kata sandi berhasil diubah! Gunakan kata sandi baru pada login berikutnya.');
    }
}
