<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    public function storeGejala(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|unique:gejala,kode',
            'nama' => 'required',
            'kategori' => 'required|in:fisik,emosional,perilaku,kognitif',
            'bobot' => 'required|numeric|min:0|max:1',
        ]);

        Gejala::create($validated);
        $this->log('CREATE_GEJALA', $validated['kode'], "Menambahkan gejala baru: " . $validated['nama']);

        return redirect()->back()->with('success', 'Gejala berhasil ditambahkan.');
    }

    public function updateGejala(Request $request, Gejala $gejala)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'kategori' => 'required|in:fisik,emosional,perilaku,kognitif',
            'bobot' => 'required|numeric|min:0|max:1',
        ]);

        $gejala->update($validated);
        $this->log('UPDATE_GEJALA', $gejala->kode, "Memperbarui gejala: " . $validated['nama']);

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
    public function storeAturan(Request $request)
    {
        $request->validate([
            'diagnosa_id' => 'required|exists:diagnosa,id',
            'cf_pakar' => 'required|numeric|min:0|max:1',
            'gejala_ids' => 'required|array',
        ]);

        try {
            DB::transaction(function() use ($request) {
                $count = Aturan::count() + 1;
                $kode = 'R' . str_pad($count, 3, '0', STR_PAD_LEFT);

                $aturan = Aturan::create([
                    'kode' => $kode,
                    'diagnosa_id' => $request->diagnosa_id,
                    'cf_pakar' => $request->cf_pakar,
                ]);

                $aturan->gejala()->attach($request->gejala_ids);
                $this->log('CREATE_ATURAN', $kode, "Menambahkan aturan baru");
            });

            return redirect()->back()->with('success', 'Aturan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan aturan: ' . $e->getMessage());
        }
    }

    public function updateAturan(Request $request, Aturan $aturan)
    {
        $request->validate([
            'diagnosa_id' => 'required|exists:diagnosa,id',
            'cf_pakar' => 'required|numeric|min:0|max:1',
            'gejala_ids' => 'required|array',
        ]);

        try {
            DB::transaction(function() use ($request, $aturan) {
                $aturan->update([
                    'diagnosa_id' => $request->diagnosa_id,
                    'cf_pakar' => $request->cf_pakar,
                ]);

                $aturan->gejala()->sync($request->gejala_ids);
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
    public function storeDiagnosa(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|unique:diagnosa,kode',
            'nama' => 'required',
            'tingkat' => 'required|in:RINGAN,SEDANG,BERAT',
            'deskripsi' => 'nullable',
            'saran' => 'nullable',
            'color' => 'required',
            'bg_light' => 'required',
        ]);

        Diagnosa::create($validated);
        $this->log('CREATE_DIAGNOSA', $validated['kode'], "Menambahkan diagnosa baru: " . $validated['nama']);

        return redirect()->back()->with('success', 'Diagnosa berhasil ditambahkan.');
    }

    public function updateDiagnosa(Request $request, Diagnosa $diagnosa)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'tingkat' => 'required|in:RINGAN,SEDANG,BERAT',
            'deskripsi' => 'nullable',
            'saran' => 'nullable',
            'color' => 'required',
            'bg_light' => 'required',
        ]);

        $diagnosa->update($validated);
        $this->log('UPDATE_DIAGNOSA', $diagnosa->kode, "Memperbarui diagnosa: " . $validated['nama']);

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
