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
        
        $history = Konsultasi::with(['user', 'diagnosa'])->orderBy('created_at', 'desc')->take(5)->get();

        return view('hrd.dashboard', compact('total_konsultasi', 'total_karyawan', 'history'));
    }
}
