<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Konsultasi;
use Carbon\Carbon;

class KaryawanController extends Controller
{
    public function index(\App\Services\HrisService $hrisService, \App\Services\RecommendationService $recommendationService)
    {
        $user = Auth::user();
        $history = Konsultasi::with('diagnosa')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $total_deteksi = $history->count();
        $last_result = $history->first();

        // ── Sapaan berdasarkan waktu ──
        $hour = Carbon::now()->hour;
        $greet = ($hour < 11) ? 'Selamat Pagi' : (($hour < 15) ? 'Selamat Siang' : (($hour < 19) ? 'Selamat Sore' : 'Selamat Malam'));

        // ── Longitudinal & Predictive Analytics ──
        $trend_points = $history->reverse()->values();
        $predicted_score = null;
        $predicted_status = 'Belum Ada Prediksi';
        $predicted_color = 'var(--color-gray-400)';
        $trend_direction = 'stable'; // up, down, stable
        $score_change = 0;
        $warning_flag = false;

        // Date & Score Arrays for ApexCharts
        $chart_dates = [];
        $chart_scores = [];

        foreach ($trend_points as $point) {
            $chart_dates[] = $point->created_at->translatedFormat('d M Y');
            $chart_scores[] = round($point->cf_final * 100, 1);
        }

        if ($total_deteksi >= 1) {
            $latest_score = $trend_points[$total_deteksi - 1]->cf_final;
            
            if ($total_deteksi >= 2) {
                $prev_score = $trend_points[$total_deteksi - 2]->cf_final;
                $score_change = $latest_score - $prev_score;
                
                if ($score_change > 0.05) {
                    $trend_direction = 'up';
                } elseif ($score_change < -0.05) {
                    $trend_direction = 'down';
                }
                
                // Early warning if score increases by >= 15% (0.15)
                if ($score_change >= 0.15) {
                    $warning_flag = true;
                }

                // Simple Linear Regression: y = mx + c
                $n = $total_deteksi;
                $sum_x = 0;
                $sum_y = 0;
                $sum_xy = 0;
                $sum_xx = 0;

                for ($i = 0; $i < $n; $i++) {
                    $x = $i + 1;
                    $y = $trend_points[$i]->cf_final;
                    $sum_x += $x;
                    $sum_y += $y;
                    $sum_xy += $x * $y;
                    $sum_xx += $x * $x;
                }

                $denominator = ($n * $sum_xx) - ($sum_x * $sum_x);
                if ($denominator != 0) {
                    $slope = (($n * $sum_xy) - ($sum_x * $sum_y)) / $denominator;
                    $intercept = ($sum_y - ($slope * $sum_x)) / $n;
                    // Project next point (n + 1)
                    $predicted_val = ($slope * ($n + 1)) + $intercept;
                    $predicted_score = max(0, min(1, $predicted_val)); // clamp between 0 and 1
                } else {
                    $predicted_score = $latest_score;
                }
            } else {
                $predicted_score = $latest_score;
            }

            // Map predicted score to likely diagnosa level
            if ($predicted_score >= 0.80) {
                $predicted_status = 'Burnout Sangat Tinggi (Severe)';
                $predicted_color = '#dc2626';
            } elseif ($predicted_score >= 0.65) {
                $predicted_status = 'Burnout Tinggi (High)';
                $predicted_color = '#ea580c';
            } elseif ($predicted_score >= 0.40) {
                $predicted_status = 'Burnout Sedang (Moderate)';
                $predicted_color = '#ca8a04';
            } else {
                $predicted_status = 'Risiko Burnout Rendah (Normal)';
                $predicted_color = '#16a34a';
            }
        }

        $hrisMetrics = $hrisService->getMetrics($user);
        $recommendations = $recommendationService->generate($user, $hrisMetrics, $last_result);

        return view('karyawan.dashboard', compact(
            'greet', 
            'total_deteksi', 
            'last_result',
            'trend_direction',
            'score_change',
            'warning_flag',
            'predicted_score',
            'predicted_status',
            'predicted_color',
            'chart_dates',
            'chart_scores',
            'hrisMetrics',
            'recommendations'
        ));
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
