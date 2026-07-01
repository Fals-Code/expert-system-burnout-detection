<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Konsultasi;
use App\Models\User;

class ReportController extends Controller
{
    public function index()
    {
        $latestConsultations = Konsultasi::with(['user.divisi', 'diagnosa'])
            ->orderByDesc('created_at')
            ->get()
            ->unique('user_id')
            ->values();

        $laporan_divisi = Divisi::orderBy('nama')->get()->map(function ($divisi) use ($latestConsultations) {
            $items = $latestConsultations->filter(fn ($consultation) => $consultation->user?->divisi_id === $divisi->id);
            $stats = ['tinggi' => 0, 'sedang' => 0, 'rendah' => 0, 'tidak' => 0];

            foreach ($items as $consultation) {
                $stats[$this->categoryKey($consultation->diagnosa?->tingkat)]++;
            }

            return array_merge([
                'divisi' => $divisi->nama,
                'total' => $items->count(),
                'suppressed' => $items->count() > 0 && $items->count() < 3,
            ], $stats);
        })->all();

        return view('hrd.reports.index', compact('laporan_divisi'));
    }

    public function employees()
    {
        $employees = User::where('role', 'karyawan')
            ->with(['divisi', 'konsultasi' => function ($query) {
                $query->latest()->with('diagnosa')->limit(1);
            }])
            ->paginate(10);

        return view('hrd.reports.employees', compact('employees'));
    }

    public function employeeHistory(User $user)
    {
        abort(403, 'HRD hanya dapat mengakses laporan agregat untuk menjaga privasi hasil check-in.');
    }

    private function categoryKey(?string $level): string
    {
        return match (strtoupper((string) $level)) {
            'TINGGI', 'SANGAT TINGGI' => 'tinggi',
            'SEDANG' => 'sedang',
            'RENDAH' => 'rendah',
            default => 'tidak',
        };
    }
}
