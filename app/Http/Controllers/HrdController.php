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
        
        $stats = [
            'tinggi' => Konsultasi::whereHas('diagnosa', fn($q) => $q->where('tingkat', 'BERAT'))->count(),
            'sedang' => Konsultasi::whereHas('diagnosa', fn($q) => $q->where('tingkat', 'SEDANG'))->count(),
            'rendah' => Konsultasi::whereHas('diagnosa', fn($q) => $q->where('tingkat', 'RINGAN'))->count(),
        ];
        
        $history = Konsultasi::with(['user', 'diagnosa'])->orderBy('created_at', 'desc')->take(5)->get();

        // Data untuk Tren Bulanan (6 Bulan Terakhir)
        $chart_trends = Konsultasi::selectRaw('MONTHNAME(created_at) as month, COUNT(*) as total')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('hrd.dashboard', compact('total_konsultasi', 'total_karyawan', 'history', 'stats', 'chart_trends'));
    }
}
