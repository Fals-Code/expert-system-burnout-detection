<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Konsultasi;
use App\Models\Diagnosa;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $latestConsultations = Konsultasi::with(['user.divisi', 'diagnosa'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('user_id');

        $divisions = Divisi::all();
        $laporan_divisi = [];

        foreach ($divisions as $div) {
            $divCons = $latestConsultations->filter(function ($c) use ($div) {
                return $c->user?->divisi_id == $div->id;
            });

            $tinggi = 0;
            $sedang = 0;
            $rendah = 0;

            foreach ($divCons as $c) {
                $tingkat = strtoupper($c->diagnosa?->tingkat ?? 'RENDAH');
                if (in_array($tingkat, ['SANGAT TINGGI', 'TINGGI'])) {
                    $tinggi++;
                } elseif ($tingkat === 'SEDANG') {
                    $sedang++;
                } else {
                    $rendah++;
                }
            }

            $laporan_divisi[] = [
                'divisi' => $div->nama,
                'total' => $divCons->count(),
                'tinggi' => $tinggi,
                'sedang' => $sedang,
                'rendah' => $rendah,
            ];
        }

        return view('hrd.reports.index', compact('laporan_divisi'));
    }

    public function employees()
    {
        $employees = User::where('role', 'karyawan')
            ->with(['divisi', 'konsultasi' => function($q) {
                $q->latest()->with('diagnosa');
            }])
            ->paginate(10);

        return view('hrd.reports.employees', compact('employees'));
    }

    public function employeeHistory(User $user)
    {
        $user->load(['divisi', 'konsultasi.diagnosa', 'konsultasi.gejala']);
        return view('hrd.reports.history', compact('user'));
    }
}
