<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AuditLog;
use App\Models\Divisi;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $divisions = Divisi::all();
        return view('profile.index', compact('user', 'divisions'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        $user->nama = $validated['nama'];
        $user->email = $validated['email'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'UPDATE_PROFILE',
            'entity' => 'User',
            'desc' => "Memperbarui profil mandiri"
        ]);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }
}
