@extends('layouts.app')

@section('title', 'Basis Pengetahuan - SanctuaryHub')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 class="page-title" style="margin: 0; font-size: 1.85rem; font-weight: 800; background: linear-gradient(135deg, var(--color-primary) 0%, #4f46e5 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Manajemen Basis Pengetahuan</h1>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 0.25rem;">Kelola fakta gejala MBI, aturan penelusuran Backward Chaining, serta resolusi konflik Certainty Factor.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.knowledge.backup') }}" class="btn-cta" style="display: inline-flex; align-items: center; gap: 6px; padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Backup KB
            </a>
            <form action="{{ route('admin.knowledge.restore') }}" method="POST" enctype="multipart/form-data" style="display: inline-flex; align-items: center; gap: 6px;" id="restoreForm">
                @csrf
                <label class="btn-cta" style="background: #0f172a; color: white; display: inline-flex; align-items: center; gap: 6px; padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 8px; cursor: pointer; margin: 0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Restore KB
                    <input type="file" name="backup_file" accept=".json" style="display: none;" onchange="if(confirm('Apakah Anda yakin ingin melakukan restore basis pengetahuan? Seluruh aturan, gejala, dan diagnosa aktif akan digantikan.')) document.getElementById('restoreForm').submit();">
                </label>
            </form>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.75rem; pointer-events: none;"></div>

    @if(session('success'))
        <div class="alert alert--success" style="margin-bottom: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span style="font-weight: 600;">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 12px; color: #991b1b; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="font-weight: 700; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                Terdapat kesalahan validasi:
            </div>
            <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.85rem; line-height: 2;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="segmented-tabs" style="margin-bottom: 2rem; background: #f1f5f9; padding: 0.35rem; border-radius: 14px; display: inline-flex; gap: 0.25rem; width: auto;">
        <button class="tab-item active" onclick="switchTab('gejala', this)" style="border-radius: 10px; font-weight: 600; padding: 0.6rem 1.25rem; border: none; cursor: pointer; transition: 0.2s;">
            Gejala MBI — {{ $gejala->count() }}
        </button>
        <button class="tab-item" onclick="switchTab('aturan', this)" style="border-radius: 10px; font-weight: 600; padding: 0.6rem 1.25rem; border: none; cursor: pointer; transition: 0.2s;">
            Aturan Inferensi — {{ $aturan->count() }}
        </button>
        <button class="tab-item" onclick="switchTab('diagnosa', this)" style="border-radius: 10px; font-weight: 600; padding: 0.6rem 1.25rem; border: none; cursor: pointer; transition: 0.2s;">
            Level Diagnosa — {{ $diagnosa->count() }}
        </button>
    </div>

    <!-- Section Gejala -->
    <div id="section-gejala" class="content-card knowledge-section" style="border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h2 class="card-title" style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1e293b;">Daftar Gejala Maslach Burnout Inventory</h2>
                <p style="color: #64748b; font-size: 0.8rem; margin-top: 0.25rem;">Skema pertanyaan yang dipetakan berdasarkan pilar EE, DP, dan PA.</p>
            </div>
            <button class="btn-cta" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 10px;" onclick="openAddGejala()">
                + Tambah Gejala
            </button>
        </div>
        <div class="table-container overflow-x-auto">
            <table class="data-table" id="gejalaTable">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Gejala</th>
                        <th>Kategori Dimensi MBI</th>
                        <th>Bobot Pakar Default</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gejala as $g)
                    <tr>
                        <td style="font-weight: 800; color: var(--color-primary);">{{ $g->kode }}</td>
                        <td style="max-width: 400px; line-height: 1.5; color: #334155; font-size: 0.9rem;">{{ $g->nama }}</td>
                        <td>
                            @php
                                $catColor = match($g->kategori) {
                                    'fisik' => ['#eff6ff', '#1e40af', 'Fisik Somatik'],
                                    'emosional' => ['#fff7ed', '#c2410c', 'Kelelahan Emosional (EE)'],
                                    'perilaku' => ['#f0fdf4', '#166534', 'Depersonalisasi (DP)'],
                                    'kognitif' => ['#faf5ff', '#6b21a8', 'Pencapaian Diri Rendah (PA)'],
                                    default => ['#f1f5f9', '#475569', 'Lainnya']
                                };
                            @endphp
                            <span class="badge" style="background: {{ $catColor[0] }}; color: {{ $catColor[1] }}; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 6px;">{{ $catColor[2] }}</span>
                        </td>
                        <td style="font-weight: 800; font-size: 0.95rem; color: #0f172a;">{{ number_format($g->bobot, 2) }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn-icon" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.4rem;" title="Edit" onclick="openEditGejala({{ $g->toJson() }})">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </button>
                                <form action="{{ route('admin.knowledge.gejala.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Hapus gejala {{ $g->kode }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon" style="background: #fff5f5; border: 1px solid #fee2e2; border-radius: 8px; padding: 0.4rem;" title="Hapus">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
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
    <div id="section-aturan" class="content-card knowledge-section" style="display: none; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h2 class="card-title" style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1e293b;">Aturan Inferensi & Logika Pakar</h2>
                <p style="color: #64748b; font-size: 0.8rem; margin-top: 0.25rem;">Sesuaikan prioritas evaluasi secara instan menggunakan drag & drop di tabel.</p>
            </div>
            <button class="btn-cta" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 10px;" onclick="openAddAturan()">
                + Tambah Aturan
            </button>
        </div>
        
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 0.75rem 1rem; border-radius: 12px; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <span style="font-size: 0.8rem; color: #475569; font-weight: 600;">Petunjuk Pakar: Urutan baris menentukan prioritas penelusuran Backward Chaining. Drag-and-drop baris untuk menyusun prioritas aturan secara otomatis.</span>
        </div>

        <div class="table-container overflow-x-auto">
            <table class="data-table" id="aturanTable">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th>Kode & Status</th>
                        <th>Level Diagnosa Target</th>
                        <th>Penyusun Gejala & Bobot Pakar</th>
                        <th>Sains Meta (CF & Threshold)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="rules-sortable-tbody">
                    @foreach($aturan->sortByDesc('prioritas') as $a)
                    <tr draggable="true" class="draggable-rule-row" data-id="{{ $a->id }}" data-prioritas="{{ $a->prioritas }}" ondragstart="handleDragStart(event)" ondragover="handleDragOver(event)" ondrop="handleDrop(event)" style="cursor: grab; transition: background 0.15s ease;">
                        <td style="text-align: center; vertical-align: middle; cursor: grab; padding: 0.75rem 0.25rem;">
                            <!-- Drag handle icon SVG -->
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;"><line x1="8" y1="9" x2="16" y2="9"></line><line x1="8" y1="12" x2="16" y2="12"></line><line x1="8" y1="15" x2="16" y2="15"></line></svg>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-weight: 800; color: #0f172a; font-size: 1rem;">{{ $a->kode }}</span>
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.7rem; border-radius: 4px; padding: 0.1rem 0.4rem;">Prio: {{ $a->prioritas }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                    <label class="switch-container" style="display: inline-flex; align-items: center; cursor: pointer;">
                                        <input type="checkbox" class="status-toggle-checkbox" data-id="{{ $a->id }}" {{ $a->is_active ? 'checked' : '' }} onchange="toggleRuleActiveState({{ $a->id }}, this)" style="display: none;">
                                        <span class="switch-slider {{ $a->is_active ? 'active' : '' }}"></span>
                                    </label>
                                    <span style="font-size: 0.75rem; font-weight: 600; color: {{ $a->is_active ? '#16a34a' : '#64748b' }}">{{ $a->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background: {{ $a->diagnosa->bg_light }}; color: {{ $a->diagnosa->color }}; font-weight: 800; padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.8rem; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.02);">
                                {{ $a->diagnosa->nama }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 6px; max-width: 420px;">
                                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                                    @foreach($a->gejala as $gj)
                                        @php
                                            $col = match($gj->kategori) {
                                                'fisik' => ['#dbeafe', '#1e40af'],
                                                'emosional' => ['#ffe4e6', '#be123c'],
                                                'perilaku' => ['#dcfce7', '#15803d'],
                                                'kognitif' => ['#f3e8ff', '#7e22ce'],
                                                default => ['#f1f5f9', '#475569']
                                            };
                                            $bobot = $gj->pivot->bobot_pakar ?? $gj->bobot;
                                        @endphp
                                        <span class="badge" style="background: {{ $col[0] }}; color: {{ $col[1] }}; font-size: 0.75rem; font-weight: 700; border: 1px solid rgba(0,0,0,0.03); border-radius: 6px; padding: 0.2rem 0.5rem; display: inline-flex; align-items: center; gap: 4px;" title="{{ $gj->nama }}">
                                            {{ $gj->kode }}
                                            <span style="opacity: 0.6; font-weight: 400;">|</span>
                                            <span style="font-weight: 800;">CF {{ number_format($bobot, 2) }}</span>
                                        </span>
                                    @endforeach
                                </div>
                                @if($a->deskripsi)
                                    <div style="font-size: 0.75rem; color: #64748b; line-height: 1.4; background: #fafafa; padding: 0.4rem 0.6rem; border-radius: 8px; border-left: 3px solid #cbd5e1; margin-top: 2px;">
                                        <strong>Rasional Pakar:</strong> {{ $a->deskripsi }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 4px; font-size: 0.85rem;">
                                <div style="display: flex; justify-content: space-between; gap: 10px;">
                                    <span style="color: #64748b;">CF Pakar Aturan:</span>
                                    <span style="font-weight: 800; color: #ea580c;">{{ number_format($a->cf_pakar, 2) }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; gap: 10px;">
                                    <span style="color: #64748b;">Ambang Pemicu:</span>
                                    <span style="font-weight: 800; color: #475569;">&ge; {{ number_format($a->min_threshold, 2) }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; align-items: center; height: 100%;">
                                <button class="btn-icon" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.4rem;" title="Edit" onclick="openEditAturan({{ $a->toJson() }})">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </button>
                                <form action="{{ route('admin.knowledge.aturan.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Hapus aturan {{ $a->kode }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon" style="background: #fff5f5; border: 1px solid #fee2e2; border-radius: 8px; padding: 0.4rem;" title="Hapus">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
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
    <div id="section-diagnosa" class="content-card knowledge-section" style="display: none; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h2 class="card-title" style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1e293b;">Daftar Diagnosa & Solusi Stres</h2>
                <p style="color: #64748b; font-size: 0.8rem; margin-top: 0.25rem;">Visualisasi level diagnosis akhir beserta rekomendasi tindakan medis/psikologis.</p>
            </div>
            <button class="btn-cta" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 10px;" onclick="openAddDiagnosa()">
                + Tambah Diagnosa
            </button>
        </div>
        <div class="table-container overflow-x-auto">
            <table class="data-table" id="diagnosaTable">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Diagnosa</th>
                        <th>Tingkat Dampak</th>
                        <th>Preview Visual UI</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($diagnosa as $d)
                    <tr>
                        <td style="font-weight: 800; color: var(--color-primary);">{{ $d->kode }}</td>
                        <td style="font-weight: 700; color: #1e293b;">{{ $d->nama }}</td>
                        <td>
                            <span class="badge" style="background: #f1f5f9; color: #334155; font-weight: 700;">{{ $d->tingkat }}</span>
                        </td>
                        <td>
                            <div style="padding: 0.35rem 0.85rem; border-radius: 8px; background: {{ $d->bg_light }}; color: {{ $d->color }}; font-size: 0.75rem; font-weight: 800; display: inline-block; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.04);">
                                {{ $d->nama }}
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn-icon" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.4rem;" title="Edit" onclick="openEditDiagnosa({{ $d->toJson() }})">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </button>
                                <form action="{{ route('admin.knowledge.diagnosa.destroy', $d->id) }}" method="POST" onsubmit="return confirm('Hapus diagnosa {{ $d->kode }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon" style="background: #fff5f5; border: 1px solid #fee2e2; border-radius: 8px; padding: 0.4rem;" title="Hapus">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
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
        <div class="modal-content" style="max-width: 650px;">
            <div class="modal-header">
                <h3 id="aturanModalTitle">Tambah Aturan</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('aturanModal')">&times;</button>
            </div>
            <form id="aturanForm" method="POST">
                @csrf
                <div id="aturanMethod"></div>
                <div class="modal-body" style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label">Kode Aturan</label>
                            <input type="text" name="kode" id="aturanKode" class="form-input" placeholder="R01" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Diagnosa Target (Goal)</label>
                            <select name="diagnosa_id" id="aturanDiagnosa" class="form-input">
                                @foreach($diagnosa as $d)
                                    <option value="{{ $d->id }}">{{ $d->nama }} ({{ $d->tingkat }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label">Certainty Factor Pakar (0 - 1.0)</label>
                            <input type="number" name="cf_pakar" id="aturanCF" class="form-input" step="0.01" min="0" max="1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ambang Batas Pemicu Aturan</label>
                            <input type="number" name="min_threshold" id="aturanMinThreshold" class="form-input" step="0.05" min="0" max="1" value="0.20" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1.25rem; align-items: center;">
                        <div class="form-group">
                            <label class="form-label">Prioritas Aturan (Makin Besar = Makin Prioritas)</label>
                            <input type="number" name="prioritas" id="aturanPrioritas" class="form-input" min="1" max="100" value="1" required>
                        </div>
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; color: #334155;">
                                <input type="checkbox" name="is_active" id="aturanIsActive" value="1" checked style="width: 18px; height: 18px;">
                                Aturan Aktif
                            </label>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Deskripsi Rationale (Penjelasan Pakar)</label>
                        <textarea name="deskripsi" id="aturanDeskripsi" class="form-input" style="height: 60px; resize: none;" placeholder="Mengapa aturan ini disusun? Tuliskan latar belakang ilmiahnya..."></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Centang Gejala & Tentukan Bobot Pakar</label>
                        <div style="max-height: 250px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0.75rem; background: #f8fafc; display: flex; flex-direction: column; gap: 0.5rem;">
                            @foreach($gejala as $g)
                                <div class="checkbox-weight-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.6rem 0.8rem; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; gap: 1rem;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; flex: 1;">
                                        <input type="checkbox" name="gejala_ids[]" value="{{ $g->id }}" id="cb-gejala-{{ $g->id }}" class="gejala-checkbox" onchange="toggleWeightInput({{ $g->id }}, this)" style="width: 18px; height: 18px;">
                                        <div style="font-size: 0.85rem;">
                                            <span style="font-weight: 800; color: var(--color-primary);">[{{ $g->kode }}]</span>
                                            <span style="color: #334155;">{{ $g->nama }}</span>
                                        </div>
                                    </label>
                                    <div id="weight-container-{{ $g->id }}" style="display: none; align-items: center; gap: 6px;">
                                        <span style="font-size: 0.7rem; font-weight: 700; color: #64748b;">Bobot:</span>
                                        <input type="number" name="bobot_pakar[{{ $g->id }}]" id="weight-input-{{ $g->id }}" class="form-input" style="width: 70px; padding: 0.25rem 0.4rem; font-size: 0.8rem; font-weight: 700; margin: 0; text-align: center;" step="0.05" min="0" max="1" value="0.80">
                                    </div>
                                </div>
                            @endforeach
                        </div>
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
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.style.pointerEvents = 'auto';
        toast.style.minWidth = '280px';
        toast.style.padding = '0.75rem 1rem';
        toast.style.borderRadius = '10px';
        toast.style.background = type === 'success' ? '#10b981' : '#ef4444';
        toast.style.color = '#fff';
        toast.style.fontWeight = '600';
        toast.style.fontSize = '0.85rem';
        toast.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.gap = '8px';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';

        const icon = document.createElement('span');
        icon.innerHTML = type === 'success' 
            ? `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`
            : `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;

        toast.appendChild(icon);
        
        const text = document.createElement('span');
        text.innerText = message;
        toast.appendChild(text);

        container.appendChild(toast);
        
        // Force reflow
        toast.offsetHeight;
        
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

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
        modal.scrollTop = 0;
        setTimeout(() => {
            modal.classList.add('active');
            modal.scrollTop = 0;
        }, 10);
    }

    function toggleWeightInput(gejalaId, checkbox) {
        const container = document.getElementById('weight-container-' + gejalaId);
        const input = document.getElementById('weight-input-' + gejalaId);
        
        if (checkbox.checked) {
            container.style.display = 'inline-flex';
            input.disabled = false;
        } else {
            container.style.display = 'none';
            input.disabled = true;
        }
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
        document.getElementById('aturanPrioritas').value = 1;
        document.getElementById('aturanMinThreshold').value = "0.20";
        document.getElementById('aturanIsActive').checked = true;
        document.getElementById('aturanDeskripsi').value = '';
        
        document.querySelectorAll('.gejala-checkbox').forEach(cb => {
            cb.checked = false;
            const gejalaId = cb.value;
            document.getElementById('weight-container-' + gejalaId).style.display = 'none';
            document.getElementById('weight-input-' + gejalaId).disabled = true;
        });
        openModal('aturanModal');
    }

    function openEditAturan(data) {
        document.getElementById('aturanModalTitle').innerText = 'Edit Aturan ' + data.kode;
        document.getElementById('aturanForm').action = "/admin/knowledge/aturan/" + data.id;
        document.getElementById('aturanMethod').innerHTML = '@method("PUT")';
        document.getElementById('aturanDiagnosa').value = data.diagnosa_id;
        document.getElementById('aturanCF').value = data.cf_pakar;
        document.getElementById('aturanPrioritas').value = data.prioritas || 1;
        document.getElementById('aturanMinThreshold').value = data.min_threshold !== undefined ? data.min_threshold : "0.20";
        document.getElementById('aturanIsActive').checked = data.is_active;
        document.getElementById('aturanDeskripsi').value = data.deskripsi || '';
        
        document.querySelectorAll('.gejala-checkbox').forEach(cb => {
            const gejalaId = cb.value;
            const match = data.gejala.find(g => g.id == gejalaId);
            if (match) {
                cb.checked = true;
                document.getElementById('weight-container-' + gejalaId).style.display = 'inline-flex';
                document.getElementById('weight-input-' + gejalaId).disabled = false;
                document.getElementById('weight-input-' + gejalaId).value = match.pivot && match.pivot.bobot_pakar !== undefined ? match.pivot.bobot_pakar : 0.8;
            } else {
                cb.checked = false;
                document.getElementById('weight-container-' + gejalaId).style.display = 'none';
                document.getElementById('weight-input-' + gejalaId).disabled = true;
            }
        });

        openModal('aturanModal');
    }

    // Quick Status Toggle Action
    function toggleRuleActiveState(ruleId, el) {
        const labelSlider = el.nextElementSibling;
        const isChecked = el.checked;
        
        if (isChecked) {
            labelSlider.classList.add('active');
        } else {
            labelSlider.classList.remove('active');
        }

        fetch('/admin/knowledge/aturan/' + ruleId + '/quick', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                is_active: isChecked
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Status aturan berhasil diubah!');
                el.parentElement.nextElementSibling.style.color = isChecked ? '#16a34a' : '#64748b';
                el.parentElement.nextElementSibling.innerText = isChecked ? 'Aktif' : 'Nonaktif';
            } else {
                showToast(data.message || 'Gagal mengubah status', 'error');
                el.checked = !isChecked; // Revert
                if (!isChecked) labelSlider.classList.add('active');
                else labelSlider.classList.remove('active');
            }
        })
        .catch(err => {
            showToast('Kesalahan koneksi ke server', 'error');
            el.checked = !isChecked; // Revert
            if (!isChecked) labelSlider.classList.add('active');
            else labelSlider.classList.remove('active');
        });
    }

    // DRAG AND DROP PRIORITY RE-ORDERING
    let dragSrcEl = null;

    function handleDragStart(e) {
        dragSrcEl = e.currentTarget;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', e.currentTarget.outerHTML);
        e.currentTarget.style.opacity = '0.4';
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }

        const targetEl = e.currentTarget;
        if (dragSrcEl && dragSrcEl !== targetEl) {
            // Swap row contents cleanly
            const parent = targetEl.parentNode;
            
            // Get all rows
            const rows = Array.from(parent.querySelectorAll('.draggable-rule-row'));
            const srcIdx = rows.indexOf(dragSrcEl);
            const targetIdx = rows.indexOf(targetEl);

            dragSrcEl.style.opacity = '1';

            if (srcIdx < targetIdx) {
                parent.insertBefore(dragSrcEl, targetEl.nextSibling);
            } else {
                parent.insertBefore(dragSrcEl, targetEl);
            }

            // Recalculate priority lists based on new relative sequence
            saveNewPrioritySequence();
        }
        return false;
    }

    function saveNewPrioritySequence() {
        const tbody = document.getElementById('rules-sortable-tbody');
        const rows = Array.from(tbody.querySelectorAll('.draggable-rule-row'));
        
        // Assign priorities descending (10 down to 1, or sequentially)
        const total = rows.length;
        const updates = [];

        rows.forEach((row, index) => {
            const ruleId = row.getAttribute('data-id');
            const newPriority = total - index; // First row gets highest priority score
            updates.push({ id: ruleId, prioritas: newPriority, rowEl: row });
        });

        // Loop updates and send quick PUT updates in sequence
        const promises = updates.map(update => {
            return fetch('/admin/knowledge/aturan/' + update.id + '/quick', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    prioritas: update.prioritas
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update visual badge inside current row
                    update.rowEl.setAttribute('data-prioritas', update.prioritas);
                    const badge = update.rowEl.querySelector('.badge');
                    if (badge && badge.innerText.includes('Prio:')) {
                        badge.innerText = 'Prio: ' + update.prioritas;
                    }
                }
            });
        });

        Promise.all(promises)
            .then(() => {
                showToast('Prioritas aturan berhasil diatur ulang sesuai urutan!');
            })
            .catch(() => {
                showToast('Gagal memperbarui beberapa prioritas aturan', 'error');
            });
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

    // Initialize DataTables
    document.addEventListener('DOMContentLoaded', function() {
        const labels = {
            placeholder: "Cari data...",
            perPage: "data per halaman",
            noRows: "Data tidak ditemukan",
            info: "Menampilkan {start} sampai {end} dari {rows} data",
        };

        new simpleDatatables.DataTable("#gejalaTable", { searchable: true, labels });
        new simpleDatatables.DataTable("#diagnosaTable", { searchable: true, labels });
        
        // Simple datatables reconstructs the table, which might disrupt HTML5 Drag Event listener.
        // Therefore, we manage sorting natively by styling and sorting manually without simple-datatables for the rules tab
        // to maintain robust drag-and-drop operations without library conflicts.
    });
</script>
@endpush

@push('styles')
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
    .checkbox-weight-item:hover {
        border-color: var(--color-primary) !important;
        background: #fdfdfd !important;
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
    
    /* CUSTOM TOGGLE SWITCH STYLE */
    .switch-container {
        position: relative;
        width: 38px;
        height: 20px;
    }
    .switch-slider {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        border-radius: 20px;
        transition: 0.3s ease;
    }
    .switch-slider:before {
        position: absolute;
        content: "";
        height: 14px; width: 14px;
        left: 3px; bottom: 3px;
        background-color: white;
        border-radius: 50%;
        transition: 0.3s ease;
    }
    .switch-slider.active {
        background-color: #10b981;
    }
    .switch-slider.active:before {
        transform: translateX(18px);
    }
    
    /* Dragging row aesthetics */
    .draggable-rule-row:active {
        background: #f1f5f9;
        cursor: grabbing;
    }
    .draggable-rule-row:hover {
        background: #faf5ff;
    }
</style>
@endpush
