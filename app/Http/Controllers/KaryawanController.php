<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Konsultasi;
use Carbon\Carbon;

class KaryawanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $history = Konsultasi::with('diagnosa')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $total_deteksi = $history->count();
        $last_result = $history->first();

        // Sapaan berdasarkan waktu
        $hour = Carbon::now()->hour;
        $greet = ($hour < 11) ? 'Selamat Pagi' : (($hour < 15) ? 'Selamat Siang' : (($hour < 19) ? 'Selamat Sore' : 'Selamat Malam'));

        return view('karyawan.dashboard', compact('greet', 'total_deteksi', 'last_result'));
    }

    public function history()
    {
        $user = Auth::user();
        $history = Konsultasi::with(['diagnosa', 'gejala'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // ── Chart Data: Trend (CF over time) ──
        $chartTrend = $history->reverse()->values()->map(function ($h) {
            return [
                'date' => $h->created_at->translatedFormat('d M Y'),
                'cf'   => round($h->cf_final, 4),
            ];
        });

        // ── Chart Data: Distribution (count per tingkat) ──
        $grouped = $history->groupBy(fn($h) => $h->diagnosa->tingkat ?? 'UNKNOWN');
        $chartDistribution = [
            'labels' => [],
            'counts' => [],
            'colors' => [],
        ];

        $colorMap = [
            'SANGAT TINGGI' => '#dc2626',
            'TINGGI'        => '#ea580c',
            'SEDANG'        => '#ca8a04',
            'RENDAH'        => '#16a34a',
        ];

        foreach (['SANGAT TINGGI', 'TINGGI', 'SEDANG', 'RENDAH'] as $tingkat) {
            if (isset($grouped[$tingkat])) {
                $chartDistribution['labels'][] = $tingkat;
                $chartDistribution['counts'][] = $grouped[$tingkat]->count();
                $chartDistribution['colors'][] = $colorMap[$tingkat] ?? '#94A3B8';
            }
        }

        return view('karyawan.history', compact('history', 'chartTrend', 'chartDistribution'));
    }
}
