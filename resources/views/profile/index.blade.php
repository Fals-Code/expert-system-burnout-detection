@extends('layouts.app')

@section('title', 'Profil Saya – BurnoutXpert')

@section('content')
    <h1 class="page-title">Pengaturan Profil</h1>

    @if(session('success'))
        <div class="alert alert--success" style="margin-bottom: 1.5rem;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 10px; color: #991b1b;">
            <div style="font-weight: 700; margin-bottom: 0.5rem;">⚠️ Terdapat kesalahan pada data yang diisi:</div>
            <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.85rem; line-height: 2;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
        <!-- Ringkasan Akun -->
        <div class="content-card" style="text-align: center; padding: 2rem;">
            <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; margin: 0 auto 1.5rem;">
                {{ strtoupper(substr($user->nama, 0, 1)) }}
            </div>
            <h2 style="margin: 0; color: var(--color-gray-800);">{{ $user->nama }}</h2>
            <div class="badge" style="margin-top: 0.5rem; background: #f1f5f9; color: #475569;">{{ ucfirst($user->role) }}</div>
            
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; text-align: left;">
                <div style="font-size: 0.8rem; color: var(--color-gray-500); text-transform: uppercase; font-weight: 700; margin-bottom: 1rem;">Detail Akun</div>
                
                <div style="margin-bottom: 0.75rem;">
                    <div style="font-size: 0.75rem; color: var(--color-gray-400);">ID Pegawai</div>
                    <div style="font-weight: 600; font-family: monospace;">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
                
                <div style="margin-bottom: 0.75rem;">
                    <div style="font-size: 0.75rem; color: var(--color-gray-400);">Unit Kerja / Divisi</div>
                    <div style="font-weight: 600;">{{ $user->divisi->nama ?? 'Bukan Karyawan' }}</div>
                </div>

                <div style="margin-bottom: 0.75rem;">
                    <div style="font-size: 0.75rem; color: var(--color-gray-400);">Bergabung Sejak</div>
                    <div style="font-weight: 600;">{{ $user->created_at ? $user->created_at->translatedFormat('d M Y') : 'Tidak diketahui' }}</div>
                </div>
            </div>
        </div>

        <!-- Form Edit -->
        <div class="content-card">
            <h3 class="card-title">Perbarui Informasi Pribadi</h3>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-input" value="{{ old('nama', $user->nama) }}" required>
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label">Alamat Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                </div>

                <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; margin-top: 2rem;">
                    <h4 style="margin: 0 0 1rem 0; font-size: 0.9rem; color: var(--color-gray-700);">🔒 Ganti Kata Sandi</h4>
                    <p style="font-size: 0.8rem; color: var(--color-gray-500); margin-bottom: 1rem;">Kosongkan jika Anda tidak ingin mengubah kata sandi.</p>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Kata Sandi Baru</label>
                        <input type="password" name="password" class="form-input" placeholder="Min. 6 karakter">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Kata Sandi</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi kata sandi baru">
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-cta" style="padding: 0.75rem 2rem;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
