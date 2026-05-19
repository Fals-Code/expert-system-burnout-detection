@extends('layouts.app')

@section('title', 'Profil Saya – BurnoutXpert')

@section('content')
@php
    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $user->nama), 0, 2)));
    $totalDeteksi = $user->konsultasi ? $user->konsultasi->count() : 0;
    $lastDeteksi = $user->konsultasi ? $user->konsultasi->sortByDesc('created_at')->first() : null;
@endphp

{{-- ── Alerts ── --}}
@if(session('success'))
    <div class="profile-alert profile-alert--success">
        <span class="profile-alert__icon" style="color: #10b981; display: inline-flex; align-items: center;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </span>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="profile-alert profile-alert--error">
        <span class="profile-alert__icon" style="color: #f59e0b; display: inline-flex; align-items: center;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </span>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="profile-alert profile-alert--error">
        <div>
            <span class="profile-alert__icon" style="color: #f59e0b; display: inline-flex; align-items: center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </span>
            <strong>Terdapat kesalahan:</strong>
            <ul class="profile-alert__list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ── Page Header ── --}}
<div class="profile-header">
    <div class="profile-header__left">
        <h1>Pengaturan Profil</h1>
        <p>Kelola informasi akun dan keamanan Anda</p>
    </div>
</div>

{{-- ── Main Grid ── --}}
<div class="profile-grid">

    {{-- ════════ Left: Identity Card ════════ --}}
    <div class="profile-identity">
        <div class="profile-identity__banner"></div>
        <div class="profile-identity__body">
            <div class="profile-avatar">
                {{ $initials }}
                <div class="profile-avatar__status"></div>
            </div>
            <div class="profile-identity__name">{{ $user->nama }}</div>
            <div class="profile-identity__role">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                {{ ucfirst($user->role) }}
            </div>

            <div class="profile-info">
                <div class="profile-info__item">
                    <div class="profile-info__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a4 4 0 0 0-8 0v2"/></svg>
                    </div>
                    <div>
                        <div class="profile-info__label">ID Pegawai</div>
                        <div class="profile-info__value" style="font-family: 'Courier New', monospace;">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</div>
                    </div>
                </div>

                <div class="profile-info__item">
                    <div class="profile-info__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <div>
                        <div class="profile-info__label">Email</div>
                        <div class="profile-info__value">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="profile-info__item">
                    <div class="profile-info__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <div class="profile-info__label">Unit Kerja / Divisi</div>
                        <div class="profile-info__value">{{ $user->divisi->nama ?? 'Bukan Karyawan' }}</div>
                    </div>
                </div>

                <div class="profile-info__item">
                    <div class="profile-info__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div>
                        <div class="profile-info__label">Bergabung Sejak</div>
                        <div class="profile-info__value">{{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : 'Tidak diketahui' }}</div>
                    </div>
                </div>

                <div class="profile-info__item">
                    <div class="profile-info__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10M18 20V4M6 20v-4"/></svg>
                    </div>
                    <div>
                        <div class="profile-info__label">Total Deteksi</div>
                        <div class="profile-info__value">{{ $totalDeteksi }} kali</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════ Right: Tabbed Content ════════ --}}
    <div class="profile-tabs">
        <div class="profile-tabs__nav">
            <button class="profile-tabs__btn active" data-tab="tab-info" id="tabBtnInfo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Informasi Pribadi
            </button>
            <button class="profile-tabs__btn" data-tab="tab-password" id="tabBtnPassword">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Ubah Kata Sandi
            </button>
        </div>

        {{-- ──────── Tab 1: Informasi Pribadi ──────── --}}
        <div class="profile-tab-panel active" id="tab-info">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                <div class="section-title" style="margin-bottom: 0;">Informasi Pribadi</div>
                <button type="button" class="btn btn-edit" id="btnEditProfile" onclick="enableEditMode()">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Profil
                </button>
            </div>
            <div class="section-desc">Data akun Anda yang terdaftar di sistem BurnoutXpert.</div>

            {{-- ── View Mode (Default) ── --}}
            <div id="profileViewMode">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <div class="form-display">{{ $user->nama }}</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat Email</label>
                        <div class="form-display">{{ $user->email }}</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <div class="form-display">{{ ucfirst($user->role) }}</div>
                        <span class="form-hint">Role dikelola oleh administrator.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Divisi</label>
                        <div class="form-display">{{ $user->divisi->nama ?? '-' }}</div>
                        <span class="form-hint">Hubungi HRD untuk perubahan divisi.</span>
                    </div>
                </div>
            </div>

            {{-- ── Edit Mode (Hidden by default) ── --}}
            <div id="profileEditMode" style="display: none;">
                <form action="{{ route('profile.update') }}" method="POST" id="profileForm">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                            <input type="text" name="nama" class="form-input" value="{{ old('nama', $user->nama) }}" required placeholder="Masukkan nama lengkap" data-original="{{ $user->nama }}" id="inputNama">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alamat Email <span class="required">*</span></label>
                            <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required placeholder="contoh@email.com" data-original="{{ $user->email }}" id="inputEmail">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-input" value="{{ ucfirst($user->role) }}" disabled>
                            <span class="form-hint">Role dikelola oleh administrator.</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Divisi</label>
                            <input type="text" class="form-input" value="{{ $user->divisi->nama ?? '-' }}" disabled>
                            <span class="form-hint">Hubungi HRD untuk perubahan divisi.</span>
                        </div>
                    </div>

                    <div class="form-actions" id="profileActions">
                        <button type="button" class="btn btn-cancel" onclick="cancelEditMode()">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Batal
                        </button>
                        <button type="submit" class="btn btn-save" id="btnSaveProfile" style="display: none;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ──────── Tab 2: Ubah Kata Sandi ──────── --}}
        <div class="profile-tab-panel" id="tab-password">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                <div class="section-title" style="margin-bottom: 0;">Keamanan Akun</div>
            </div>
            <div class="section-desc">Kelola kata sandi Anda untuk menjaga keamanan akun.</div>

            {{-- ── View Mode (Default) ── --}}
            <div id="passwordViewMode">
                <div class="security-card">
                    <div class="security-card__header">
                        <div class="security-card__shield">
                            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                        </div>
                        <div>
                            <div class="security-card__title">Kata Sandi Akun</div>
                            <div class="security-card__subtitle">Kata sandi digunakan untuk autentikasi login ke sistem</div>
                        </div>
                    </div>

                    <div class="security-card__body">
                        <div class="security-card__row">
                            <div class="security-card__row-icon">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <div>
                                <div class="security-card__row-label">Kata Sandi</div>
                                <div class="security-card__row-value">••••••••••••</div>
                            </div>
                        </div>
                        <div class="security-card__row">
                            <div class="security-card__row-icon">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div>
                                <div class="security-card__row-label">Terakhir Diperbarui</div>
                                <div class="security-card__row-value">{{ $user->updated_at ? $user->updated_at->translatedFormat('d F Y, H:i') : 'Tidak diketahui' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="security-card__footer">
                        <button type="button" class="btn btn-edit" id="btnStartChangePassword" onclick="enablePasswordEditMode()" style="width: 100%; justify-content: center; padding: 0.75rem;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Ubah Kata Sandi
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Edit Mode (Hidden by default) ── --}}
            <div id="passwordEditMode" style="display: none;">
                <div class="security-notice">
                    <div class="security-notice__icon">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div class="security-notice__text">
                        <strong>Konfirmasi Identitas</strong>
                        Masukkan kata sandi lama Anda terlebih dahulu untuk memverifikasi identitas sebelum melakukan perubahan.
                    </div>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" id="passwordForm">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Kata Sandi Saat Ini <span class="required">*</span></label>
                            <div class="password-input-group">
                                <input type="password" name="current_password" class="form-input" required placeholder="Masukkan kata sandi saat ini" id="currentPassword">
                                <button type="button" class="password-toggle" onclick="togglePassword('currentPassword', this)">
                                    <svg class="eye-open" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kata Sandi Baru <span class="required">*</span></label>
                            <div class="password-input-group">
                                <input type="password" name="password" class="form-input" required placeholder="Min. 6 karakter" id="newPassword" oninput="checkPasswordStrength(this.value)">
                                <button type="button" class="password-toggle" onclick="togglePassword('newPassword', this)">
                                    <svg class="eye-open" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <div class="password-strength" id="passwordStrength">
                                <div class="password-strength__bar"><div class="password-strength__fill"></div></div>
                                <div class="password-strength__text">Masukkan kata sandi baru</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Konfirmasi Kata Sandi Baru <span class="required">*</span></label>
                            <div class="password-input-group">
                                <input type="password" name="password_confirmation" class="form-input" required placeholder="Ulangi kata sandi baru" id="confirmPassword" oninput="checkPasswordMatch()">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', this)">
                                    <svg class="eye-open" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <span class="form-hint" id="matchHint"></span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-cancel" onclick="cancelPasswordEditMode()">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Batal
                        </button>
                        <button type="submit" class="btn btn-save" id="btnChangePassword">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Simpan Kata Sandi Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Tab Switching ──
    const tabBtns = document.querySelectorAll('.profile-tabs__btn');
    const tabPanels = document.querySelectorAll('.profile-tab-panel');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.dataset.tab;

            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanels.forEach(p => p.classList.remove('active'));

            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });

    // Auto-switch to password tab + edit mode if there are password-related errors
    @if($errors->has('current_password') || $errors->has('password'))
        document.querySelector('[data-tab="tab-password"]').click();
        setTimeout(() => enablePasswordEditMode(), 100);
    @endif

    // Auto-switch to edit mode if there are profile-related errors
    @if($errors->has('nama') || $errors->has('email'))
        enableEditMode();
    @endif

    // ── Change Detection for Profile Form ──
    const inputNama = document.getElementById('inputNama');
    const inputEmail = document.getElementById('inputEmail');
    
    if (inputNama && inputEmail) {
        [inputNama, inputEmail].forEach(input => {
            input.addEventListener('input', detectChanges);
        });
    }

    // SweetAlert for password change confirmation
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            
            Swal.fire({
                title: 'Konfirmasi Ubah Sandi',
                text: 'Apakah Anda yakin ingin mengubah kata sandi? Anda akan tetap login setelah perubahan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#F4845F',
                cancelButtonColor: '#94A3B8',
                confirmButtonText: 'Ya, Ubah Sandi',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
});

// ── Edit Mode Toggle ──
function enableEditMode() {
    document.getElementById('profileViewMode').style.display = 'none';
    document.getElementById('profileEditMode').style.display = 'block';
    document.getElementById('btnEditProfile').style.display = 'none';
    
    // Focus the first input
    const firstInput = document.getElementById('inputNama');
    if (firstInput) {
        setTimeout(() => firstInput.focus(), 100);
    }
}

function cancelEditMode() {
    // Reset form to original values
    const form = document.getElementById('profileForm');
    form.reset();

    // Restore original values from data attributes
    document.getElementById('inputNama').value = document.getElementById('inputNama').dataset.original;
    document.getElementById('inputEmail').value = document.getElementById('inputEmail').dataset.original;
    
    // Switch back to view mode
    document.getElementById('profileViewMode').style.display = 'block';
    document.getElementById('profileEditMode').style.display = 'none';
    document.getElementById('btnEditProfile').style.display = 'inline-flex';
    document.getElementById('btnSaveProfile').style.display = 'none';
}

// ── Detect Changes in Profile Form ──
function detectChanges() {
    const inputNama = document.getElementById('inputNama');
    const inputEmail = document.getElementById('inputEmail');
    const btnSave = document.getElementById('btnSaveProfile');
    
    const namaChanged = inputNama.value !== inputNama.dataset.original;
    const emailChanged = inputEmail.value !== inputEmail.dataset.original;
    
    if (namaChanged || emailChanged) {
        btnSave.style.display = 'inline-flex';
        btnSave.style.animation = 'tabFadeIn 0.3s ease-out';
    } else {
        btnSave.style.display = 'none';
    }
}

// ── Password Edit Mode Toggle ──
function enablePasswordEditMode() {
    document.getElementById('passwordViewMode').style.display = 'none';
    document.getElementById('passwordEditMode').style.display = 'block';
    
    // Focus the current password input
    const currentPw = document.getElementById('currentPassword');
    if (currentPw) {
        setTimeout(() => currentPw.focus(), 100);
    }
}

function cancelPasswordEditMode() {
    // Reset form and UI
    const form = document.getElementById('passwordForm');
    if (form) form.reset();
    resetPasswordUI();
    
    // Switch back to view mode
    document.getElementById('passwordViewMode').style.display = 'block';
    document.getElementById('passwordEditMode').style.display = 'none';
}

// ── Toggle Password Visibility ──
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const eyeOpen = btn.querySelector('.eye-open');
    const eyeClosed = btn.querySelector('.eye-closed');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
    } else {
        input.type = 'password';
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
    }
}

// ── Password Strength Checker ──
function checkPasswordStrength(password) {
    const container = document.getElementById('passwordStrength');
    const text = container.querySelector('.password-strength__text');
    
    container.className = 'password-strength';
    
    if (password.length === 0) {
        text.textContent = 'Masukkan kata sandi baru';
        return;
    }

    let score = 0;
    if (password.length >= 6) score++;
    if (password.length >= 10) score++;
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    if (score <= 1) {
        container.classList.add('strength-weak');
        text.textContent = 'Lemah – Tambahkan huruf besar, angka, atau simbol';
    } else if (score === 2) {
        container.classList.add('strength-fair');
        text.textContent = 'Cukup – Bisa lebih kuat lagi';
    } else if (score === 3) {
        container.classList.add('strength-good');
        text.textContent = 'Baik – Hampir sempurna';
    } else {
        container.classList.add('strength-strong');
        text.textContent = 'Kuat – Kata sandi sangat aman!';
    }

    checkPasswordMatch();
}

// ── Password Match Checker ──
function checkPasswordMatch() {
    const newPass = document.getElementById('newPassword').value;
    const confirmPass = document.getElementById('confirmPassword').value;
    const hint = document.getElementById('matchHint');

    if (confirmPass.length === 0) {
        hint.textContent = '';
        hint.style.color = '';
        return;
    }

    if (newPass === confirmPass) {
        hint.innerHTML = '<span style="display:inline-flex; align-items:center; gap:4px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Kata sandi cocok</span>';
        hint.style.color = '#10B981';
    } else {
        hint.innerHTML = '<span style="display:inline-flex; align-items:center; gap:4px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> Kata sandi belum cocok</span>';
        hint.style.color = '#EF4444';
    }
}

// ── Reset Password UI ──
function resetPasswordUI() {
    const container = document.getElementById('passwordStrength');
    container.className = 'password-strength';
    container.querySelector('.password-strength__text').textContent = 'Masukkan kata sandi baru';
    document.getElementById('matchHint').textContent = '';
    document.getElementById('matchHint').style.color = '';
}
</script>
@endpush
