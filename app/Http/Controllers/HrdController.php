<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Konsultasi;
use App\Models\User;

class HrdController extends Controller
{
    public function index()
    {
        $total_konsultasi = Konsultasi::count();
        $total_karyawan = User::where('role', 'karyawan')->count();
        
        $latestConsultations = Konsultasi::with(['user', 'diagnosa'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('user_id');

        $tinggi = 0; $sedang = 0; $rendah = 0;
        foreach ($latestConsultations as $c) {
            $tingkat = strtoupper($c->diagnosa?->tingkat ?? 'RENDAH');
            if (in_array($tingkat, ['SANGAT TINGGI', 'TINGGI'])) {
                $tinggi++;
            } elseif ($tingkat === 'SEDANG') {
                $sedang++;
            } else {
                $rendah++;
            }
        }

        $stats = [
            'tinggi' => $tinggi,
            'sedang' => $sedang,
            'rendah' => $rendah,
        ];
        
        $history = Konsultasi::with(['user', 'diagnosa'])->orderBy('created_at', 'desc')->take(5)->get();

        // Data untuk Tren Bulanan (6 Bulan Terakhir)
        $chart_trends = Konsultasi::selectRaw('MONTHNAME(created_at) as month, COUNT(*) as total')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderByRaw('MIN(created_at) asc')
            ->get();

        return view('hrd.dashboard', compact('total_konsultasi', 'total_karyawan', 'history', 'stats', 'chart_trends'));
    }
}
