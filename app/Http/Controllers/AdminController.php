<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Gejala;
use App\Models\Aturan;
use App\Models\AuditLog;

class AdminController extends Controller
{
    public function index()
    {
        $total_users = User::count();
        $total_gejala = Gejala::count();
        $total_aturan = Aturan::count();
        $total_logs = AuditLog::count();
        
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->take(5)->get();
        
        // Data Komposisi Divisi
        $divisi_stats = \App\Models\Divisi::withCount('users')->get();

        return view('admin.dashboard', compact('total_users', 'total_gejala', 'total_aturan', 'total_logs', 'logs', 'divisi_stats'));
    }

    public function logs()
    {
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.logs', compact('logs'));
    }
}
