<?php

namespace App\Http\Controllers;

use App\Models\Konsultasi;
use App\Models\User;

class HrdController extends Controller
{
    public function index()
    {
        $total_konsultasi = Konsultasi::count();
        $total_karyawan = User::where('role', 'karyawan')->count();

        $latestConsultations = Konsultasi::with(['user.divisi', 'diagnosa'])
            ->orderByDesc('created_at')
            ->get()
            ->unique('user_id')
            ->values();

        $stats = $this->emptyStats();

        foreach ($latestConsultations as $consultation) {
            $stats[$this->categoryKey($consultation->diagnosa?->tingkat)]++;
        }

        $history = Konsultasi::with(['user.divisi', 'diagnosa'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $chart_trends = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = Konsultasi::query()
                ->whereBetween('created_at', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth(),
                ])
                ->count();

            $chart_trends->push([
                'month' => $month->translatedFormat('M Y'),
                'total' => $count,
            ]);
        }

        return view('hrd.dashboard', compact('total_konsultasi', 'total_karyawan', 'history', 'stats', 'chart_trends'));
    }

    private function emptyStats(): array
    {
        return [
            'tinggi' => 0,
            'sedang' => 0,
            'rendah' => 0,
            'tidak' => 0,
        ];
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
