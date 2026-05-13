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

        return view('karyawan.history', compact('history'));
    }
}
