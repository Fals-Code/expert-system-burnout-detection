<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Gejala;
use App\Models\Aturan;
use App\Models\AuditLog;
use App\Models\Diagnosa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        if ($request->boolean('refresh_knowledge_base')) {
            $this->forgetKnowledgeBaseCache();

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'REFRESH_KNOWLEDGE_CACHE',
                'entity' => 'KnowledgeBase',
                'desc' => 'Admin menyegarkan cache basis pengetahuan sistem pakar dari dashboard.',
            ]);

            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Basis pengetahuan berhasil disegarkan. Perubahan bobot pakar dan threshold terbaru sudah dapat digunakan.');
        }

        $total_users = User::count();
        $total_gejala = Gejala::count();
        $total_aturan = Aturan::count();
        $total_logs = AuditLog::count();
        
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->take(5)->get();
        
        // Data Komposisi Divisi
        $divisi_stats = \App\Models\Divisi::withCount('users')->get();

        // ── Advanced Analytics Calculations ──
        $sixMonthsAgo = now()->subMonths(6);
        $consultations = \App\Models\Konsultasi::with(['user.divisi', 'diagnosa'])
            ->where('created_at', '>=', $sixMonthsAgo)
            ->get();

        // 1. Risk Distribution (Latest diagnosis for each employee)
        $latestConsultations = \App\Models\Konsultasi::with(['user.divisi', 'diagnosa'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('user_id');

        $riskDistribution = [
            'TIDAK BURNOUT' => 0,
            'RENDAH' => 0,
            'SEDANG' => 0,
            'TINGGI' => 0,
            'SANGAT TINGGI' => 0,
        ];

        foreach ($latestConsultations as $c) {
            $tingkat = strtoupper($c->diagnosa?->tingkat ?? 'TIDAK BURNOUT');
            if (array_key_exists($tingkat, $riskDistribution)) {
                $riskDistribution[$tingkat]++;
            }
        }

        // 2. Division Average Stress Score
        $divisionLabels = [];
        $divisionAverages = [];
        foreach ($divisi_stats as $div) {
            $divCons = $latestConsultations->filter(function ($c) use ($div) {
                return $c->user?->divisi_id == $div->id;
            });
            $avgScore = $divCons->count() > 0 ? $divCons->avg('cf_final') * 100 : 0;
            $divisionLabels[] = $div->nama;
            $divisionAverages[] = round($avgScore, 1);
        }

        // 3. Monthly Stress Average Trend (Last 6 Months)
        $trendMonths = [];
        $trendAverages = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthName = $monthDate->translatedFormat('F Y');
            $monthKey = $monthDate->format('Y-m');
            
            $monthCons = $consultations->filter(function ($c) use ($monthKey) {
                return $c->created_at->format('Y-m') === $monthKey;
            });
            
            $avg = $monthCons->count() > 0 ? $monthCons->avg('cf_final') * 100 : 0;
            $trendMonths[] = $monthName;
            $trendAverages[] = round($avg, 1);
        }

        // 4. Early Warning High-Risk Employees
        $earlyAlerts = [];
        foreach ($latestConsultations as $c) {
            $tingkat = strtoupper($c->diagnosa?->tingkat ?? 'TIDAK BURNOUT');
            if ($tingkat === 'TINGGI' || $tingkat === 'SANGAT TINGGI') {
                $earlyAlerts[] = [
                    'nama' => $c->user?->nama ?? 'Karyawan',
                    'divisi' => $c->user?->divisi?->nama ?? 'N/A',
                    'tingkat' => $c->diagnosa?->tingkat ?? 'RENDAH',
                    'score' => round($c->cf_final * 100, 1),
                    'date' => $c->created_at->translatedFormat('d M Y'),
                    'color' => $c->diagnosa?->color ?? '#dc2626'
                ];
            }
        }

        return view('admin.dashboard', compact(
            'total_users', 
            'total_gejala', 
            'total_aturan', 
            'total_logs', 
            'logs', 
            'divisi_stats',
            'riskDistribution',
            'divisionLabels',
            'divisionAverages',
            'trendMonths',
            'trendAverages',
            'earlyAlerts'
        ));
    }

    public function logs()
    {
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.logs', compact('logs'));
    }

    private function forgetKnowledgeBaseCache(): void
    {
        Cache::forget('aturan_active_rules_base64');
        Cache::forget('diagnosa_ordered_base64');
        Cache::forget('diagnosa_default_rendah_base64');
        Cache::forget('diagnosa_default_tidak_burnout_base64');

        Diagnosa::query()
            ->pluck('id')
            ->each(function ($id) {
                Cache::forget("aturan_by_diagnosa_{$id}_base64");
            });
    }
}
