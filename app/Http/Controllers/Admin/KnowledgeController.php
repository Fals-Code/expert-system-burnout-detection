<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGejalaRequest;
use App\Http\Requests\StoreDiagnosaRequest;
use App\Http\Requests\StoreAturanRequest;
use App\Models\Gejala;
use App\Models\Aturan;
use App\Models\Diagnosa;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
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
        $this->log('CREATE_GEJALA', $gejala->kode, "Menambahkan gejala baru: " . $gejala->nama);

        return redirect()->back()->with('success', 'Gejala berhasil ditambahkan.');
    }

    public function updateGejala(StoreGejalaRequest $request, Gejala $gejala)
    {
        $gejala->update($request->validated());
        $this->log('UPDATE_GEJALA', $gejala->kode, "Memperbarui gejala: " . $gejala->nama);

        return redirect()->back()->with('success', 'Gejala berhasil diperbarui.');
    }

    public function destroyGejala(Gejala $gejala)
    {
        $kode = $gejala->kode;
        $gejala->delete();
        $this->log('DELETE_GEJALA', $kode, "Menghapus gejala");

        return redirect()->back()->with('success', 'Gejala berhasil dihapus.');
    }

    // --- ATURAN ---
    public function storeAturan(StoreAturanRequest $request)
    {
        try {
            DB::transaction(function() use ($request) {
                $count = Aturan::count() + 1;
                $kode = $request->kode ?? 'R' . str_pad($count, 3, '0', STR_PAD_LEFT);

                $aturan = Aturan::create([
                    'kode' => $kode,
                    'diagnosa_id' => $request->diagnosa_id,
                    'cf_pakar' => $request->cf_pakar,
                ]);

                // Attach gejala dengan bobot_pakar masing-masing
                $syncData = [];
                foreach ($request->gejala_ids as $index => $gejalaId) {
                    $syncData[$gejalaId] = [
                        'bobot_pakar' => $request->bobot_pakar[$gejalaId] ?? 0.5
                    ];
                }
                
                $aturan->gejala()->attach($syncData);
                $this->log('CREATE_ATURAN', $kode, "Menambahkan aturan baru");
            });

            return redirect()->back()->with('success', 'Aturan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan aturan: ' . $e->getMessage());
        }
    }

    public function updateAturan(StoreAturanRequest $request, Aturan $aturan)
    {
        try {
            DB::transaction(function() use ($request, $aturan) {
                $aturan->update([
                    'diagnosa_id' => $request->diagnosa_id,
                    'cf_pakar' => $request->cf_pakar,
                ]);

                $syncData = [];
                foreach ($request->gejala_ids as $index => $gejalaId) {
                    $syncData[$gejalaId] = [
                        'bobot_pakar' => $request->bobot_pakar[$gejalaId] ?? 0.5
                    ];
                }

                $aturan->gejala()->sync($syncData);
                $this->log('UPDATE_ATURAN', $aturan->kode, "Memperbarui aturan");
            });

            return redirect()->back()->with('success', 'Aturan berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui aturan: ' . $e->getMessage());
        }
    }

    public function destroyAturan(Aturan $aturan)
    {
        $kode = $aturan->kode;
        $aturan->delete();
        $this->log('DELETE_ATURAN', $kode, "Menghapus aturan");

        return redirect()->back()->with('success', 'Aturan berhasil dihapus.');
    }

    // --- DIAGNOSA ---
    public function storeDiagnosa(StoreDiagnosaRequest $request)
    {
        $diagnosa = Diagnosa::create($request->validated());
        $this->log('CREATE_DIAGNOSA', $diagnosa->kode, "Menambahkan diagnosa baru: " . $diagnosa->nama);

        return redirect()->back()->with('success', 'Diagnosa berhasil ditambahkan.');
    }

    public function updateDiagnosa(StoreDiagnosaRequest $request, Diagnosa $diagnosa)
    {
        $diagnosa->update($request->validated());
        $this->log('UPDATE_DIAGNOSA', $diagnosa->kode, "Memperbarui diagnosa: " . $diagnosa->nama);

        return redirect()->back()->with('success', 'Diagnosa berhasil diperbarui.');
    }

    public function destroyDiagnosa(Diagnosa $diagnosa)
    {
        $kode = $diagnosa->kode;
        $diagnosa->delete();
        $this->log('DELETE_DIAGNOSA', $kode, "Menghapus diagnosa");

        return redirect()->back()->with('success', 'Diagnosa berhasil dihapus.');
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
