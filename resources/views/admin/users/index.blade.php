@extends('layouts.app')

@section('title', 'Kelola Pengguna - SanctuaryHub')

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
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                {{ session('success') }}
            </div>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert--error" style="margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 10px; color: #991b1b;">
            <div style="font-weight: 700; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                Periksa kembali data Anda:
            </div>
            <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.85rem; line-height: 2;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 2rem;">
        <div class="content-card stat-card" style="border-bottom: 4px solid #3b82f6;">
            <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Karyawan</div>
                <div class="stat-value">{{ $stats['karyawan'] }}</div>
            </div>
        </div>
        <div class="content-card stat-card" style="border-bottom: 4px solid #f59e0b;">
            <div class="stat-icon" style="background: #fffbeb; color: #f59e0b;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            </div>
            <div class="stat-info">
                <div class="stat-label">Tim HRD</div>
                <div class="stat-value">{{ $stats['hrd'] }}</div>
            </div>
        </div>
        <div class="content-card stat-card" style="border-bottom: 4px solid #ef4444;">
            <div class="stat-icon" style="background: #fef2f2; color: #ef4444;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
            <div class="stat-info">
                <div class="stat-label">Administrator</div>
                <div class="stat-value">{{ $stats['admin'] }}</div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="table-container overflow-x-auto">
            <table class="data-table" id="usersTable">
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
                                <button class="btn-icon" title="Edit Data" onclick="openEditUser({{ $u->toJson() }})">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </button>
                                @if($u->id !== Auth::id())
                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus user {{ $u->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon" title="Hapus Akun">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
            {{ $users->links() }}
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
        modal.scrollTop = 0;
        setTimeout(() => {
            modal.classList.add('active');
            modal.scrollTop = 0;
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

    // Initialize DataTable
    document.addEventListener('DOMContentLoaded', function() {
        const dataTable = new simpleDatatables.DataTable("#usersTable", {
            searchable: true,
            fixedHeight: false,
            perPage: 10,
            labels: {
                placeholder: "Cari pengguna...",
                perPage: "data per halaman",
                noRows: "Data tidak ditemukan",
                info: "Menampilkan {start} sampai {end} dari {rows} data",
            }
        });
    });
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
