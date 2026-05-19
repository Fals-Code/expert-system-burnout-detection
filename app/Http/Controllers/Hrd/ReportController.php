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
        $stats = DB::table('users')
            ->join('konsultasi', 'users.id', '=', 'konsultasi.user_id')
            ->join('diagnosa', 'konsultasi.diagnosa_id', '=', 'diagnosa.id')
            ->join('divisi', 'users.divisi_id', '=', 'divisi.id')
            ->select(
                'divisi.id as divisi_id',
                'divisi.nama as divisi_nama',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when diagnosa.tingkat = 'BERAT' then 1 else 0 end) as tinggi"),
                DB::raw("sum(case when diagnosa.tingkat = 'SEDANG' then 1 else 0 end) as sedang"),
                DB::raw("sum(case when diagnosa.tingkat = 'RINGAN' then 1 else 0 end) as rendah")
            )
            ->groupBy('divisi.id', 'divisi.nama')
            ->get()
            ->keyBy('divisi_id');

        $divisions = Divisi::all();
        $laporan_divisi = [];

        foreach ($divisions as $div) {
            $divStats = $stats->get($div->id);
            $laporan_divisi[] = [
                'divisi' => $div->nama,
                'total' => $divStats ? ($divStats->total ?? 0) : 0,
                'tinggi' => $divStats ? ($divStats->tinggi ?? 0) : 0,
                'sedang' => $divStats ? ($divStats->sedang ?? 0) : 0,
                'rendah' => $divStats ? ($divStats->rendah ?? 0) : 0,
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
