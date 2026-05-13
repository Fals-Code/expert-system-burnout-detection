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
        $divisions = Divisi::all();
        $laporan_divisi = [];

        foreach ($divisions as $div) {
            $stats = DB::table('konsultasi')
                ->join('users', 'konsultasi.user_id', '=', 'users.id')
                ->join('diagnosa', 'konsultasi.diagnosa_id', '=', 'diagnosa.id')
                ->where('users.divisi_id', $div->id)
                ->select(
                    DB::raw('count(*) as total'),
                    DB::raw("sum(case when diagnosa.tingkat = 'BERAT' then 1 else 0 end) as tinggi"),
                    DB::raw("sum(case when diagnosa.tingkat = 'SEDANG' then 1 else 0 end) as sedang"),
                    DB::raw("sum(case when diagnosa.tingkat = 'RINGAN' then 1 else 0 end) as rendah")
                )
                ->first();

            $laporan_divisi[] = [
                'divisi' => $div->nama,
                'total' => $stats->total ?? 0,
                'tinggi' => $stats->tinggi ?? 0,
                'sedang' => $stats->sedang ?? 0,
                'rendah' => $stats->rendah ?? 0,
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
