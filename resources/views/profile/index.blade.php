@extends('layouts.app')

@section('title', 'Profil & Privasi – Sanctuary Hub')

@section('content')
@php
    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $user->nama), 0, 2)));
    $totalCheckin = $user->konsultasi ? $user->konsultasi->count() : 0;
@endphp

@if(session('success'))
    <div class="profile-alert profile-alert--success">
        <span class="profile-alert__icon" style="color:#10b981;">✓</span>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="profile-alert profile-alert--error">
        <span class="profile-alert__icon" style="color:#f59e0b;">i</span>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="profile-alert profile-alert--error">
        <div>
            <strong>Periksa kembali isian berikut:</strong>
            <ul class="profile-alert__list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="profile-header" style="background:linear-gradient(135deg,#eff6ff,#ffffff 55%,#ecfdf5); border:1px solid #dbeafe; border-radius:24px; padding:1.5rem; margin-bottom:1.5rem;">
    <div class="profile-header__left">
        <p style="display:inline-flex;align-items:center;margin:0 0 .6rem;padding:.35rem .75rem;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-size:.75rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase;">
            Profil & Privasi
        </p>
        <h1 style="margin-bottom:.5rem;">Akun Pribadi</h1>
        <p style="max-width:760px;color:#64748b;line-height:1.7;">
            Kelola informasi akun, keamanan login, dan pahami bagaimana data check-in Anda digunakan. Halaman ini dibuat sebagai ruang kendali pribadi, bukan tempat memberi label kondisi Anda. Hebat, akun pun akhirnya punya sedikit martabat.
        </p>
    </div>
</div>

<div class="profile-grid">
    <aside class="profile-identity">
        <div class="profile-identity__banner" style="background:linear-gradient(135deg,#2563eb,#14b8a6);"></div>
        <div class="profile-identity__body">
            <div class="profile-avatar">{{ $initials }}<div class="profile-avatar__status"></div></div>
            <div class="profile-identity__name">{{ $user->nama }}</div>
            <div class="profile-identity__role">{{ ucfirst($user->role) }}</div>

            <div class="profile-info">
                <div class="profile-info__item">
                    <div class="profile-info__icon">#</div>
                    <div>
                        <div class="profile-info__label">ID Akun</div>
                        <div class="profile-info__value" style="font-family:'Courier New',monospace;">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</div>
                    </div>
                </div>

                <div class="profile-info__item">
                    <div class="profile-info__icon">@</div>
                    <div>
                        <div class="profile-info__label">Email</div>
                        <div class="profile-info__value">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="profile-info__item">
                    <div class="profile-info__icon">◇</div>
                    <div>
                        <div class="profile-info__label">Unit Kerja / Divisi</div>
                        <div class="profile-info__value">{{ $user->divisi->nama ?? 'Bukan Karyawan' }}</div>
                    </div>
                </div>

                <div class="profile-info__item">
                    <div class="profile-info__icon">✓</div>
                    <div>
                        <div class="profile-info__label">Total Check-in</div>
                        <div class="profile-info__value">{{ $totalCheckin }} kali</div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <section class="profile-tabs">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:18px;padding:1rem;">
                <div style="font-weight:900;color:#1d4ed8;margin-bottom:.35rem;">Yang Anda kendalikan</div>
                <p style="margin:0;color:#475569;font-size:.86rem;line-height:1.65;">Nama, email, kata sandi, dan akses ke riwayat check-in pribadi.</p>
            </div>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:18px;padding:1rem;">
                <div style="font-weight:900;color:#166534;margin-bottom:.35rem;">Yang tidak menjadi penilaian</div>
                <p style="margin:0;color:#475569;font-size:.86rem;line-height:1.65;">Check-in tidak ditampilkan sebagai ranking performa individu pada halaman karyawan.</p>
            </div>
        </div>

        <div class="profile-tab-panel active" style="display:block; margin-bottom:1rem;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:.75rem;flex-wrap:wrap;">
                <div>
                    <div class="section-title" style="margin-bottom:.25rem;">Informasi Akun</div>
                    <div class="section-desc">Data akun yang dipakai untuk login dan identitas dasar di sistem.</div>
                </div>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" id="profileForm">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="nama" class="form-input" value="{{ old('nama', $user->nama) }}" required placeholder="Masukkan nama lengkap">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required placeholder="contoh@email.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-input" value="{{ ucfirst($user->role) }}" disabled>
                        <span class="form-hint">Role dikelola administrator agar akses sistem tetap aman.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Divisi</label>
                        <input type="text" class="form-input" value="{{ $user->divisi->nama ?? '-' }}" disabled>
                        <span class="form-hint">Hubungi HRD jika data unit kerja perlu diperbarui.</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-save">Simpan Informasi Akun</button>
                </div>
            </form>
        </div>

        <div class="profile-tab-panel active" style="display:block; margin-bottom:1rem;">
            <div class="section-title" style="margin-bottom:.25rem;">Keamanan Login</div>
            <div class="section-desc">Gunakan kata sandi yang kuat agar riwayat check-in pribadi tetap aman.</div>

            <div class="security-card" style="margin:1rem 0;">
                <div class="security-card__header">
                    <div class="security-card__shield">✓</div>
                    <div>
                        <div class="security-card__title">Status Akun Aman</div>
                        <div class="security-card__subtitle">Terakhir diperbarui: {{ $user->updated_at ? $user->updated_at->translatedFormat('d F Y, H:i') : 'Tidak diketahui' }}</div>
                    </div>
                </div>
            </div>

            <form action="{{ route('profile.password') }}" method="POST" id="passwordForm">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Kata Sandi Saat Ini <span class="required">*</span></label>
                        <input type="password" name="current_password" class="form-input" required placeholder="Masukkan kata sandi saat ini">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kata Sandi Baru <span class="required">*</span></label>
                        <input type="password" name="password" class="form-input" required placeholder="Min. 6 karakter">
                        <span class="form-hint">Lebih baik gunakan kombinasi huruf, angka, dan simbol.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Kata Sandi Baru <span class="required">*</span></label>
                        <input type="password" name="password_confirmation" class="form-input" required placeholder="Ulangi kata sandi baru">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-save">Simpan Kata Sandi Baru</button>
                </div>
            </form>
        </div>

        <div class="profile-tab-panel active" style="display:block; background:#f8fafc;border:1px solid #e2e8f0;border-radius:18px;padding:1rem;">
            <div class="section-title" style="margin-bottom:.35rem;">Ide Pengembangan Profil</div>
            <ul style="margin:0;padding-left:1.2rem;color:#64748b;line-height:1.8;font-size:.9rem;">
                <li>Tambahkan preferensi notifikasi check-in mingguan.</li>
                <li>Tambahkan pengaturan privasi untuk tampilan insight pribadi.</li>
                <li>Tambahkan tombol “Minta Dukungan” yang meminta persetujuan sebelum menghubungi HR.</li>
                <li>Tambahkan ekspor riwayat pribadi dalam format ringkasan, bukan data mentah yang menakutkan.</li>
            </ul>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            if (typeof Swal === 'undefined') return;
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Simpan Kata Sandi Baru?',
                text: 'Perubahan ini hanya memengaruhi keamanan login Anda.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#94A3B8',
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    }
});
</script>
@endpush

<style>
    @media (max-width: 900px) {
        [style*="grid-template-columns:1fr 1fr"] { grid-template-columns:1fr !important; }
    }
</style>
