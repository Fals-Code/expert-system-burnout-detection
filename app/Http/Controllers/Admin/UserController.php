<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Divisi;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('divisi')->orderBy('id', 'desc')->get();
        $divisions = Divisi::orderBy('nama')->get();
        
        $stats = [
            'karyawan' => User::where('role', 'karyawan')->count(),
            'hrd' => User::where('role', 'hrd')->count(),
            'admin' => User::where('role', 'admin')->count(),
        ];

        return view('admin.users.index', compact('users', 'divisions', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:karyawan,hrd,admin',
            'divisi_id' => 'nullable|exists:divisi,id',
        ]);

        $validated['password'] = Hash::make($request->password);
        
        $user = User::create($validated);
        $this->log('CREATE_USER', $user->id, "Menambahkan pengguna baru: " . $user->nama);

        return redirect()->back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'role' => 'required|in:karyawan,hrd,admin',
            'divisi_id' => 'nullable|exists:divisi,id',
            'password' => 'nullable|min:6',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $this->log('UPDATE_USER', $user->id, "Memperbarui data pengguna: " . $user->nama);

        return redirect()->back()->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $id = $user->id;
        $nama = $user->nama;
        $user->delete();
        $this->log('DELETE_USER', $id, "Menghapus pengguna: " . $nama);

        return redirect()->back()->with('success', 'Pengguna berhasil dihapus.');
    }

    protected function log($action, $entity, $desc)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity' => $entity,
            'desc' => $desc,
        ]);
    }
}
