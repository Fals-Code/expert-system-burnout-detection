<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HrdController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Karyawan Routes
Route::middleware(['auth', 'role:karyawan'])->prefix('karyawan')->group(function () {
    Route::get('/dashboard', [KaryawanController::class, 'index'])->name('karyawan.dashboard');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});

// HRD Routes
Route::middleware(['auth', 'role:hrd'])->prefix('hrd')->group(function () {
    Route::get('/dashboard', [HrdController::class, 'index'])->name('hrd.dashboard');
});
