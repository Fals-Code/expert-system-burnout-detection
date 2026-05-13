@extends('layouts.app')

@section('title', 'Basis Pengetahuan – BurnoutXpert')

@section('content')
    <h1 class="page-title">Manajemen Basis Pengetahuan</h1>

    @if(session('success'))
        <div class="alert alert--success" style="margin-bottom: 1.5rem;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 10px; color: #991b1b;">
            <div style="font-weight: 700; margin-bottom: 0.5rem;">⚠️ Terdapat kesalahan validasi:</div>
            <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.85rem; line-height: 2;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="segmented-tabs" style="margin-bottom: 1.5rem;">
        <button class="tab-item active" onclick="switchTab('gejala', this)">
            Gejala (Fakta) — {{ $gejala->count() }}
        </button>
        <button class="tab-item" onclick="switchTab('aturan', this)">
            Aturan (Rules) — {{ $aturan->count() }}
        </button>
        <button class="tab-item" onclick="switchTab('diagnosa', this)">
            Diagnosa (Goals) — {{ $diagnosa->count() }}
        </button>
    </div>

    <!-- Section Gejala -->
    <div id="section-gejala" class="content-card knowledge-section">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="card-title" style="margin: 0;">Daftar Gejala Burnout</h2>
            <button class="btn-cta" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="openAddGejala()">
                + Tambah Gejala
            </button>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Gejala</th>
                        <th>Kategori</th>
                        <th>Bobot</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gejala as $g)
                    <tr>
                        <td style="font-weight: 700; color: var(--color-primary);">{{ $g->kode }}</td>
                        <td style="max-width: 300px;">{{ $g->nama }}</td>
                        <td>
                            @php
                                $catColor = match($g->kategori) {
                                    'fisik' => ['#dbeafe', '#1e40af'],
                                    'emosional' => ['#fef3c7', '#92400e'],
                                    'perilaku' => ['#dcfce7', '#166534'],
                                    'kognitif' => ['#f3e8ff', '#6b21a8'],
                                    default => ['#f1f5f9', '#475569']
                                };
                            @endphp
                            <span class="badge" style="background: {{ $catColor[0] }}; color: {{ $catColor[1] }};">{{ ucfirst($g->kategori) }}</span>
                        </td>
                        <td style="font-weight: 700;">{{ number_format($g->bobot, 2) }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn-icon" title="Edit" onclick="openEditGejala({{ $g->toJson() }})">✏️</button>
                                <form action="{{ route('admin.knowledge.gejala.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Hapus gejala {{ $g->kode }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon" title="Hapus">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section Aturan -->
    <div id="section-aturan" class="content-card knowledge-section" style="display: none;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="card-title" style="margin: 0;">Aturan Diagnosa (Rules)</h2>
            <button class="btn-cta" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="openAddAturan()">
                + Tambah Aturan
            </button>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Diagnosa Target</th>
                        <th>Kumpulan Gejala</th>
                        <th>CF Pakar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aturan as $a)
                    <tr>
                        <td style="font-weight: 700; color: var(--color-primary);">{{ $a->kode }}</td>
                        <td>
                            <span class="badge" style="background: {{ $a->diagnosa->bg_light }}; color: {{ $a->diagnosa->color }}; font-weight: 700;">
                                {{ $a->diagnosa->nama }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.25rem; max-width: 400px;">
                                @foreach($a->gejala as $gj)
                                    <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.7rem; border: 1px solid #e2e8f0;">{{ $gj->kode }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td style="font-weight: 700; color: #f59e0b;">{{ number_format($a->cf_pakar, 2) }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn-icon" title="Edit" onclick="openEditAturan({{ $a->toJson() }})">✏️</button>
                                <form action="{{ route('admin.knowledge.aturan.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Hapus aturan {{ $a->kode }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon" title="Hapus">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section Diagnosa -->
    <div id="section-diagnosa" class="content-card knowledge-section" style="display: none;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="card-title" style="margin: 0;">Daftar Diagnosa & Solusi</h2>
            <button class="btn-cta" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="openAddDiagnosa()">
                + Tambah Diagnosa
            </button>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Diagnosa</th>
                        <th>Tingkat</th>
                        <th>Preview UI</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($diagnosa as $d)
                    <tr>
                        <td style="font-weight: 700; color: var(--color-primary);">{{ $d->kode }}</td>
                        <td>{{ $d->nama }}</td>
                        <td>
                            <span class="badge" style="background: var(--color-gray-100); color: var(--color-gray-700);">{{ $d->tingkat }}</span>
                        </td>
                        <td>
                            <div style="padding: 0.25rem 0.75rem; border-radius: 50px; background: {{ $d->bg_light }}; color: {{ $d->color }}; font-size: 0.75rem; font-weight: 800; display: inline-block;">
                                {{ $d->nama }}
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn-icon" title="Edit" onclick="openEditDiagnosa({{ $d->toJson() }})">✏️</button>
                                <form action="{{ route('admin.knowledge.diagnosa.destroy', $d->id) }}" method="POST" onsubmit="return confirm('Hapus diagnosa {{ $d->kode }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon" title="Hapus">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Gejala -->
    <div class="modal-overlay" id="gejalaModal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 id="gejalaModalTitle">Tambah Gejala</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('gejalaModal')">&times;</button>
            </div>
            <form id="gejalaForm" method="POST">
                @csrf
                <div id="gejalaMethod"></div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Kode Gejala</label>
                        <input type="text" name="kode" id="gejalaKode" class="form-input" placeholder="G021" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Nama Gejala</label>
                        <textarea name="nama" id="gejalaNama" class="form-input" style="height: 80px; resize: none;" required></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" id="gejalaKategori" class="form-input">
                            <option value="fisik">Fisik</option>
                            <option value="emosional">Emosional</option>
                            <option value="perilaku">Perilaku</option>
                            <option value="kognitif">Kognitif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bobot Pakar (0 - 1.0)</label>
                        <input type="number" name="bobot" id="gejalaBobot" class="form-input" step="0.01" min="0" max="1" required>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" class="btn-nav" onclick="closeModal('gejalaModal')">Batal</button>
                    <button type="submit" class="btn-cta">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Aturan -->
    <div class="modal-overlay" id="aturanModal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 id="aturanModalTitle">Tambah Aturan</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('aturanModal')">&times;</button>
            </div>
            <form id="aturanForm" method="POST">
                @csrf
                <div id="aturanMethod"></div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Diagnosa Target (Goal)</label>
                        <select name="diagnosa_id" id="aturanDiagnosa" class="form-input">
                            @foreach($diagnosa as $d)
                                <option value="{{ $d->id }}">{{ $d->nama }} ({{ $d->tingkat }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Pilih Gejala Terkait</label>
                        <div style="max-height: 250px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0.75rem; background: #fff;">
                            @foreach($gejala as $g)
                                <label class="checkbox-item" style="display: flex; align-items: center; padding: 0.5rem; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: 0.2s;">
                                    <input type="checkbox" name="gejala_ids[]" value="{{ $g->id }}" class="gejala-checkbox" style="width: 18px; height: 18px; margin-right: 12px;">
                                    <div style="font-size: 0.85rem;">
                                        <span style="font-weight: 700; color: var(--color-primary); margin-right: 8px;">[{{ $g->kode }}]</span>
                                        <span style="color: #475569;">{{ $g->nama }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <small style="color: #64748b; margin-top: 0.5rem; display: block;">Centang semua gejala yang harus terpenuhi untuk aturan ini.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Certainty Factor (CF) Pakar (0 - 1.0)</label>
                        <input type="number" name="cf_pakar" id="aturanCF" class="form-input" step="0.01" min="0" max="1" required>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" class="btn-nav" onclick="closeModal('aturanModal')">Batal</button>
                    <button type="submit" class="btn-cta">Simpan Aturan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Diagnosa -->
    <div class="modal-overlay" id="diagnosaModal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 id="diagnosaModalTitle">Tambah Diagnosa</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('diagnosaModal')">&times;</button>
            </div>
            <form id="diagnosaForm" method="POST">
                @csrf
                <div id="diagnosaMethod"></div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label">Kode</label>
                            <input type="text" name="kode" id="diagnosaKode" class="form-input" placeholder="P001" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama Diagnosa</label>
                            <input type="text" name="nama" id="diagnosaNama" class="form-input" required>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Tingkat Burnout</label>
                        <select name="tingkat" id="diagnosaTingkat" class="form-input">
                            <option value="RINGAN">RINGAN</option>
                            <option value="SEDANG">SEDANG</option>
                            <option value="BERAT">BERAT</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Deskripsi Kondisi</label>
                        <textarea name="deskripsi" id="diagnosaDeskripsi" class="form-input" style="height: 100px; resize: none;"></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Saran Penanganan</label>
                        <textarea name="saran" id="diagnosaSaran" class="form-input" style="height: 100px; resize: none;"></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Warna Teks (Hex)</label>
                            <input type="text" name="color" id="diagnosaColor" class="form-input" placeholder="#ef4444" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Warna BG (Hex)</label>
                            <input type="text" name="bg_light" id="diagnosaBg" class="form-input" placeholder="#fee2e2" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" class="btn-nav" onclick="closeModal('diagnosaModal')">Batal</button>
                    <button type="submit" class="btn-cta">Simpan Diagnosa</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function switchTab(target, el) {
        document.querySelectorAll('.tab-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');
        document.querySelectorAll('.knowledge-section').forEach(s => s.style.display = 'none');
        document.getElementById('section-' + target).style.display = 'block';
    }

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

    // Gejala Logic
    function openAddGejala() {
        document.getElementById('gejalaModalTitle').innerText = 'Tambah Gejala Baru';
        document.getElementById('gejalaForm').action = "{{ route('admin.knowledge.gejala.store') }}";
        document.getElementById('gejalaMethod').innerHTML = '';
        document.getElementById('gejalaKode').readOnly = false;
        document.getElementById('gejalaForm').reset();
        openModal('gejalaModal');
    }

    function openEditGejala(data) {
        document.getElementById('gejalaModalTitle').innerText = 'Edit Gejala ' + data.kode;
        document.getElementById('gejalaForm').action = "/admin/knowledge/gejala/" + data.id;
        document.getElementById('gejalaMethod').innerHTML = '@method("PUT")';
        document.getElementById('gejalaKode').value = data.kode;
        document.getElementById('gejalaKode').readOnly = true;
        document.getElementById('gejalaNama').value = data.nama;
        document.getElementById('gejalaKategori').value = data.kategori;
        document.getElementById('gejalaBobot').value = data.bobot;
        openModal('gejalaModal');
    }

    // Aturan Logic
    function openAddAturan() {
        document.getElementById('aturanModalTitle').innerText = 'Tambah Aturan Baru';
        document.getElementById('aturanForm').action = "{{ route('admin.knowledge.aturan.store') }}";
        document.getElementById('aturanMethod').innerHTML = '';
        document.getElementById('aturanForm').reset();
        document.querySelectorAll('.gejala-checkbox').forEach(cb => cb.checked = false);
        openModal('aturanModal');
    }

    function openEditAturan(data) {
        document.getElementById('aturanModalTitle').innerText = 'Edit Aturan ' + data.kode;
        document.getElementById('aturanForm').action = "/admin/knowledge/aturan/" + data.id;
        document.getElementById('aturanMethod').innerHTML = '@method("PUT")';
        document.getElementById('aturanDiagnosa').value = data.diagnosa_id;
        document.getElementById('aturanCF').value = data.cf_pakar;
        
        document.querySelectorAll('.gejala-checkbox').forEach(cb => {
            cb.checked = data.gejala.some(g => g.id == cb.value);
        });

        openModal('aturanModal');
    }

    // Diagnosa Logic
    function openAddDiagnosa() {
        document.getElementById('diagnosaModalTitle').innerText = 'Tambah Diagnosa Baru';
        document.getElementById('diagnosaForm').action = "{{ route('admin.knowledge.diagnosa.store') }}";
        document.getElementById('diagnosaMethod').innerHTML = '';
        document.getElementById('diagnosaKode').readOnly = false;
        document.getElementById('diagnosaForm').reset();
        openModal('diagnosaModal');
    }

    function openEditDiagnosa(data) {
        document.getElementById('diagnosaModalTitle').innerText = 'Edit Diagnosa ' + data.kode;
        document.getElementById('diagnosaForm').action = "/admin/knowledge/diagnosa/" + data.id;
        document.getElementById('diagnosaMethod').innerHTML = '@method("PUT")';
        document.getElementById('diagnosaKode').value = data.kode;
        document.getElementById('diagnosaKode').readOnly = true;
        document.getElementById('diagnosaNama').value = data.nama;
        document.getElementById('diagnosaTingkat').value = data.tingkat;
        document.getElementById('diagnosaDeskripsi').value = data.deskripsi;
        document.getElementById('diagnosaSaran').value = data.saran;
        document.getElementById('diagnosaColor').value = data.color;
        document.getElementById('diagnosaBg').value = data.bg_light;
        openModal('diagnosaModal');
    }
</script>
@endpush

<style>
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
    .checkbox-item:hover {
        background: #f8fafc;
    }
    .modal-close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #64748b;
    }
    .modal-close-btn:hover {
        color: #1e293b;
    }
</style>
