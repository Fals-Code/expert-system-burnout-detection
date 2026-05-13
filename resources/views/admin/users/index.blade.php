@extends('layouts.app')

@section('title', 'Kelola Pengguna – BurnoutXpert')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin: 0;">Manajemen Pengguna</h1>
        <button class="btn-cta" onclick="openAddUser()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 8px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Tambah User Baru
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert--success" style="margin-bottom: 1.5rem;">
            ✅ {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert--error" style="margin-bottom: 1.5rem;">
            ❌ {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 10px; color: #991b1b;">
            <div style="font-weight: 700; margin-bottom: 0.5rem;">⚠️ Periksa kembali data Anda:</div>
            <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.85rem; line-height: 2;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 2rem;">
        <div class="content-card stat-card" style="text-align: center; border-bottom: 4px solid #3b82f6;">
            <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;">👤</div>
            <div class="stat-value">{{ $stats['karyawan'] }}</div>
            <div class="stat-label">Total Karyawan</div>
        </div>
        <div class="content-card stat-card" style="text-align: center; border-bottom: 4px solid #f59e0b;">
            <div class="stat-icon" style="background: #fffbeb; color: #f59e0b;">🧑‍💼</div>
            <div class="stat-value">{{ $stats['hrd'] }}</div>
            <div class="stat-label">Tim HRD</div>
        </div>
        <div class="content-card stat-card" style="text-align: center; border-bottom: 4px solid #ef4444;">
            <div class="stat-icon" style="background: #fef2f2; color: #ef4444;">🛡️</div>
            <div class="stat-value">{{ $stats['admin'] }}</div>
            <div class="stat-label">Administrator</div>
        </div>
    </div>

    <div class="content-card">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Peran / Role</th>
                        <th>Unit / Divisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem;">
                                    {{ strtoupper(substr($u->nama, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--color-gray-800);">{{ $u->nama }}</div>
                                    <div style="font-size: 0.75rem; color: var(--color-gray-500);">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleBadge = match($u->role) {
                                    'admin' => ['#fee2e2', '#991b1b', 'Administrator'],
                                    'hrd' => ['#fef3c7', '#92400e', 'HR Manager'],
                                    'karyawan' => ['#dcfce7', '#166534', 'Karyawan'],
                                    default => ['#f1f5f9', '#475569', 'User']
                                };
                            @endphp
                            <span class="badge" style="background: {{ $roleBadge[0] }}; color: {{ $roleBadge[1] }}; font-weight: 700;">
                                {{ $roleBadge[2] }}
                            </span>
                        </td>
                        <td>
                            <div style="color: var(--color-gray-600); font-size: 0.9rem;">
                                {{ $u->divisi->nama ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn-icon" title="Edit Data" onclick="openEditUser({{ $u->toJson() }})">✏️</button>
                                @if($u->id !== Auth::id())
                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus user {{ $u->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon" title="Hapus Akun">🗑️</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal User -->
    <div class="modal-overlay" id="userModal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 id="userModalTitle">Tambah Pengguna</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('userModal')">&times;</button>
            </div>
            <form id="userForm" method="POST">
                @csrf
                <div id="userMethod"></div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" id="userNama" class="form-input" placeholder="Masukkan nama lengkap..." required>
                    </div>
                    <div class="form-group" id="emailGroup" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" id="userEmail" class="form-input" placeholder="contoh@perusahaan.com" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" id="pwLabel">Kata Sandi</label>
                        <input type="text" name="password" id="userPassword" class="form-input" placeholder="Min. 6 karakter">
                        <small id="pwHint" style="color: #64748b; display: none; margin-top: 4px; display: block;">* Kosongkan jika tidak ingin mengubah password.</small>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Peran Sistem</label>
                        <select name="role" id="userRole" class="form-input">
                            <option value="karyawan">Karyawan</option>
                            <option value="hrd">HRD</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit / Divisi</label>
                        <select name="divisi_id" id="userDivisi" class="form-input">
                            <option value="">- Tanpa Unit Kerja -</option>
                            @foreach($divisions as $d)
                                <option value="{{ $d->id }}">{{ $d->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" class="btn-nav" onclick="closeModal('userModal')">Batal</button>
                    <button type="submit" class="btn-cta">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        setTimeout(() => {
            document.getElementById(id).style.display = 'none';
        }, 300);
    }

    function openModal(id) {
        const modal = document.getElementById(id);
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
    }

    function openAddUser() {
        document.getElementById('userModalTitle').innerText = 'Tambah Pengguna Baru';
        document.getElementById('userForm').action = "{{ route('admin.users.store') }}";
        document.getElementById('userMethod').innerHTML = '';
        document.getElementById('userForm').reset();
        document.getElementById('emailGroup').style.display = 'block';
        document.getElementById('userEmail').required = true;
        document.getElementById('userPassword').required = true;
        document.getElementById('pwHint').style.display = 'none';
        openModal('userModal');
    }

    function openEditUser(data) {
        document.getElementById('userModalTitle').innerText = 'Edit Data Pengguna';
        document.getElementById('userForm').action = "/admin/users/" + data.id;
        document.getElementById('userMethod').innerHTML = '@method("PUT")';
        document.getElementById('userNama').value = data.nama;
        document.getElementById('userRole').value = data.role;
        document.getElementById('userDivisi').value = data.divisi_id || '';
        document.getElementById('emailGroup').style.display = 'none';
        document.getElementById('userEmail').required = false;
        document.getElementById('userPassword').required = false;
        document.getElementById('pwHint').style.display = 'block';
        openModal('userModal');
    }
</script>
@endpush

<style>
    .stat-card {
        padding: 1.5rem;
        transition: transform 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 1rem;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 900;
        color: var(--color-primary);
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    .stat-label {
        font-size: 0.85rem;
        color: var(--color-gray-500);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .modal-overlay {
        transition: opacity 0.3s ease;
        opacity: 0;
    }
    .modal-overlay.active {
        opacity: 1;
    }
    .modal-content {
        transform: translateY(-20px);
        transition: transform 0.3s ease;
    }
    .modal-overlay.active .modal-content {
        transform: translateY(0);
    }
    .modal-close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #64748b;
    }
</style>
