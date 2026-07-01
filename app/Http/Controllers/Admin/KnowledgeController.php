<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAturanRequest;
use App\Http\Requests\StoreDiagnosaRequest;
use App\Http\Requests\StoreGejalaRequest;
use App\Models\Aturan;
use App\Models\AuditLog;
use App\Models\Diagnosa;
use App\Models\Gejala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KnowledgeController extends Controller
{
    public function index()
    {
        $gejala = Gejala::orderBy('kode')->get();
        $aturan = Aturan::with(['diagnosa', 'gejala'])->orderBy('kode')->get();
        $diagnosa = Diagnosa::orderBy('id')->get();

        return view('admin.knowledge.index', compact('gejala', 'aturan', 'diagnosa'));
    }

    // --- GEJALA ---
    public function storeGejala(StoreGejalaRequest $request)
    {
        $gejala = Gejala::create($request->validated());
        $this->log('CREATE_GEJALA', $gejala->kode, 'Menambahkan gejala baru', null, $gejala->only(['kode', 'nama', 'kategori', 'bobot']));

        return redirect()->back()->with('success', 'Gejala berhasil ditambahkan.');
    }

    public function updateGejala(StoreGejalaRequest $request, Gejala $gejala)
    {
        $before = $gejala->only(['kode', 'nama', 'kategori', 'bobot']);
        $gejala->update($request->validated());
        $this->log('UPDATE_GEJALA', $gejala->kode, 'Memperbarui gejala', $before, $gejala->fresh()->only(['kode', 'nama', 'kategori', 'bobot']));

        return redirect()->back()->with('success', 'Gejala berhasil diperbarui.');
    }

    public function destroyGejala(Gejala $gejala)
    {
        if ($gejala->aturan()->exists() || $gejala->konsultasi()->exists()) {
            return redirect()->back()->with('error', 'Gejala tidak dapat dihapus karena masih digunakan oleh rule atau hasil konsultasi.');
        }

        $kode = $gejala->kode;
        $before = $gejala->only(['kode', 'nama', 'kategori', 'bobot']);
        $gejala->delete();
        $this->log('DELETE_GEJALA', $kode, 'Menghapus gejala', $before, null);

        return redirect()->back()->with('success', 'Gejala berhasil dihapus.');
    }

    // --- ATURAN ---
    public function storeAturan(StoreAturanRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $count = Aturan::count() + 1;
                $kode = $request->kode ?? 'R'.str_pad($count, 3, '0', STR_PAD_LEFT);

                $aturan = Aturan::create([
                    'kode' => $kode,
                    'diagnosa_id' => $request->diagnosa_id,
                    'cf_pakar' => $request->cf_pakar,
                    'prioritas' => $request->prioritas,
                    'is_active' => $request->has('is_active'),
                    'deskripsi' => $request->deskripsi,
                    'min_threshold' => $request->min_threshold,
                ]);

                // Attach gejala dengan bobot_pakar masing-masing
                $syncData = [];
                foreach ($request->gejala_ids as $index => $gejalaId) {
                    $syncData[$gejalaId] = [
                        'bobot_pakar' => $request->bobot_pakar[$gejalaId] ?? 0.5,
                    ];
                }

                $aturan->gejala()->attach($syncData);
                $this->log('CREATE_ATURAN', $kode, 'Menambahkan aturan baru', null, $aturan->load('gejala')->toArray());
            });

            return redirect()->back()->with('success', 'Aturan berhasil ditambahkan.');
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()->with('error', 'Gagal menambahkan aturan. Periksa premis, CF pakar, dan threshold.');
        }
    }

    public function updateAturan(StoreAturanRequest $request, Aturan $aturan)
    {
        try {
            DB::transaction(function () use ($request, $aturan) {
                $before = $aturan->load('gejala')->toArray();
                $aturan->update([
                    'diagnosa_id' => $request->diagnosa_id,
                    'cf_pakar' => $request->cf_pakar,
                    'prioritas' => $request->prioritas,
                    'is_active' => $request->has('is_active'),
                    'deskripsi' => $request->deskripsi,
                    'min_threshold' => $request->min_threshold,
                ]);

                $syncData = [];
                foreach ($request->gejala_ids as $index => $gejalaId) {
                    $syncData[$gejalaId] = [
                        'bobot_pakar' => $request->bobot_pakar[$gejalaId] ?? 0.5,
                    ];
                }

                $aturan->gejala()->sync($syncData);
                $this->log('UPDATE_ATURAN', $aturan->kode, 'Memperbarui aturan', $before, $aturan->fresh()->load('gejala')->toArray());
            });

            return redirect()->back()->with('success', 'Aturan berhasil diperbarui.');
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()->with('error', 'Gagal memperbarui aturan. Periksa premis, CF pakar, dan threshold.');
        }
    }

    public function quickUpdateAturan(Request $request, Aturan $aturan)
    {
        $validated = $request->validate([
            'prioritas' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
            'min_threshold' => ['sometimes', 'numeric', 'min:0', 'max:1'],
        ]);

        try {
            $data = [];
            if (array_key_exists('prioritas', $validated)) {
                $data['prioritas'] = (int) $validated['prioritas'];
            }
            if (array_key_exists('is_active', $validated)) {
                $data['is_active'] = (bool) $validated['is_active'];
            }
            if (array_key_exists('min_threshold', $validated)) {
                $data['min_threshold'] = (float) $validated['min_threshold'];
            }

            $before = $aturan->only(['prioritas', 'is_active', 'min_threshold']);
            $aturan->update($data);
            $this->log('QUICK_UPDATE_ATURAN', $aturan->kode, 'Memperbarui cepat atribut aturan', $before, $aturan->fresh()->only(['prioritas', 'is_active', 'min_threshold']));

            return response()->json([
                'success' => true,
                'message' => 'Aturan berhasil diperbarui secara instan.',
                'aturan' => $aturan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui aturan.',
            ], 500);
        }
    }

    public function destroyAturan(Aturan $aturan)
    {
        $kode = $aturan->kode;
        $before = $aturan->load('gejala')->toArray();
        $aturan->delete();
        $this->log('DELETE_ATURAN', $kode, 'Menghapus aturan', $before, null);

        return redirect()->back()->with('success', 'Aturan berhasil dihapus.');
    }

    // --- DIAGNOSA ---
    public function storeDiagnosa(StoreDiagnosaRequest $request)
    {
        $diagnosa = Diagnosa::create($request->validated());
        $this->log('CREATE_DIAGNOSA', $diagnosa->kode, 'Menambahkan diagnosa baru', null, $diagnosa->only(['kode', 'nama', 'tingkat', 'deskripsi', 'saran']));

        return redirect()->back()->with('success', 'Diagnosa berhasil ditambahkan.');
    }

    public function updateDiagnosa(StoreDiagnosaRequest $request, Diagnosa $diagnosa)
    {
        $before = $diagnosa->only(['kode', 'nama', 'tingkat', 'deskripsi', 'saran']);
        $diagnosa->update($request->validated());
        $this->log('UPDATE_DIAGNOSA', $diagnosa->kode, 'Memperbarui diagnosa', $before, $diagnosa->fresh()->only(['kode', 'nama', 'tingkat', 'deskripsi', 'saran']));

        return redirect()->back()->with('success', 'Diagnosa berhasil diperbarui.');
    }

    public function destroyDiagnosa(Diagnosa $diagnosa)
    {
        if ($diagnosa->aturan()->exists() || $diagnosa->konsultasi()->exists()) {
            return redirect()->back()->with('error', 'Diagnosis tidak dapat dihapus karena masih digunakan oleh rule atau hasil konsultasi.');
        }

        $kode = $diagnosa->kode;
        $before = $diagnosa->only(['kode', 'nama', 'tingkat']);
        $diagnosa->delete();
        $this->log('DELETE_DIAGNOSA', $kode, 'Menghapus diagnosa', $before, null);

        return redirect()->back()->with('success', 'Diagnosa berhasil dihapus.');
    }

    public function backupKnowledgeBase()
    {
        $data = [
            'diagnosa' => DB::table('diagnosa')->get(),
            'gejala' => DB::table('gejala')->get(),
            'aturan' => DB::table('aturan')->get(),
            'aturan_gejala' => DB::table('aturan_gejala')->get(),
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT);
        $filename = 'burnoutxpert_kb_backup_'.date('Y_m_d_His').'.json';

        $this->log('BACKUP_KB', 'ALL', 'Melakukan backup basis pengetahuan');

        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function restoreKnowledgeBase(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json,txt',
        ]);

        try {
            $fileContent = file_get_contents($request->file('backup_file')->getRealPath());
            $data = json_decode($fileContent, true);

            if (! isset($data['diagnosa']) || ! isset($data['gejala']) || ! isset($data['aturan']) || ! isset($data['aturan_gejala'])) {
                return redirect()->back()->with('error', 'Format backup JSON tidak valid.');
            }

            DB::transaction(function () use ($data) {
                $driver = DB::getDriverName();
                if ($driver === 'sqlite') {
                    DB::statement('PRAGMA foreign_keys = OFF;');
                } else {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                }

                DB::table('aturan_gejala')->truncate();
                DB::table('aturan')->truncate();
                DB::table('gejala')->truncate();
                DB::table('diagnosa')->truncate();

                foreach ($data['diagnosa'] as $row) {
                    DB::table('diagnosa')->insert((array) $row);
                }

                foreach ($data['gejala'] as $row) {
                    DB::table('gejala')->insert((array) $row);
                }

                foreach ($data['aturan'] as $row) {
                    DB::table('aturan')->insert((array) $row);
                }

                foreach ($data['aturan_gejala'] as $row) {
                    DB::table('aturan_gejala')->insert((array) $row);
                }

                if ($driver === 'sqlite') {
                    DB::statement('PRAGMA foreign_keys = ON;');
                } else {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                }

                $this->log('RESTORE_KB', 'ALL', 'Melakukan restore basis pengetahuan dari file backup');
            });

            // Flush cache immediately
            Cache::flush();

            return redirect()->back()->with('success', 'Basis pengetahuan berhasil di-restore dan cache telah dibersihkan.');
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()->with('error', 'Gagal merestore basis pengetahuan. Pastikan file JSON sesuai skema backup.');
        }
    }

    protected function log($action, $entity, $desc, ?array $before = null, ?array $after = null)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity' => $entity,
            'desc' => json_encode([
                'message' => $desc,
                'before' => $before,
                'after' => $after,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    }
}
