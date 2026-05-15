<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HrdController;
use App\Http\Controllers\DeteksiController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Shared Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/help', [\App\Http\Controllers\HelpController::class, 'index'])->name('help');
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Karyawan Routes
Route::middleware(['auth', 'role:karyawan'])->prefix('karyawan')->group(function () {
    Route::get('/dashboard', [KaryawanController::class, 'index'])->name('karyawan.dashboard');
    Route::get('/deteksi/intro', [\App\Http\Controllers\DeteksiController::class, 'intro'])->name('karyawan.deteksi.intro');
    Route::get('/deteksi', [\App\Http\Controllers\DeteksiController::class, 'index'])->name('karyawan.deteksi');
    Route::post('/deteksi', [\App\Http\Controllers\DeteksiController::class, 'next'])->name('karyawan.deteksi.next');
    Route::get('/hasil', [\App\Http\Controllers\DeteksiController::class, 'showResult'])->name('karyawan.hasil');
    Route::get('/hasil/download/{id}', [\App\Http\Controllers\DeteksiController::class, 'downloadReport'])->name('karyawan.laporan.download');
    Route::get('/history', [KaryawanController::class, 'history'])->name('karyawan.history');
    Route::get('/deteksi/reset', [\App\Http\Controllers\DeteksiController::class, 'reset'])->name('karyawan.deteksi.reset');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/logs', [AdminController::class, 'logs'])->name('admin.logs');
    
    // Knowledge Base
    Route::get('/knowledge', [\App\Http\Controllers\Admin\KnowledgeController::class, 'index'])->name('admin.knowledge');
    
    Route::post('/knowledge/gejala', [\App\Http\Controllers\Admin\KnowledgeController::class, 'storeGejala'])->name('admin.knowledge.gejala.store');
    Route::put('/knowledge/gejala/{gejala}', [\App\Http\Controllers\Admin\KnowledgeController::class, 'updateGejala'])->name('admin.knowledge.gejala.update');
    Route::delete('/knowledge/gejala/{gejala}', [\App\Http\Controllers\Admin\KnowledgeController::class, 'destroyGejala'])->name('admin.knowledge.gejala.destroy');
    
    Route::post('/knowledge/aturan', [\App\Http\Controllers\Admin\KnowledgeController::class, 'storeAturan'])->name('admin.knowledge.aturan.store');
    Route::put('/knowledge/aturan/{aturan}', [\App\Http\Controllers\Admin\KnowledgeController::class, 'updateAturan'])->name('admin.knowledge.aturan.update');
    Route::delete('/knowledge/aturan/{aturan}', [\App\Http\Controllers\Admin\KnowledgeController::class, 'destroyAturan'])->name('admin.knowledge.aturan.destroy');
    
    Route::post('/knowledge/diagnosa', [\App\Http\Controllers\Admin\KnowledgeController::class, 'storeDiagnosa'])->name('admin.knowledge.diagnosa.store');
    Route::put('/knowledge/diagnosa/{diagnosa}', [\App\Http\Controllers\Admin\KnowledgeController::class, 'updateDiagnosa'])->name('admin.knowledge.diagnosa.update');
    Route::delete('/knowledge/diagnosa/{diagnosa}', [\App\Http\Controllers\Admin\KnowledgeController::class, 'destroyDiagnosa'])->name('admin.knowledge.diagnosa.destroy');
    
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users');
    Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
});

// HRD Routes
Route::middleware(['auth', 'role:hrd'])->prefix('hrd')->group(function () {
    Route::get('/dashboard', [HrdController::class, 'index'])->name('hrd.dashboard');
    Route::get('/reports', [\App\Http\Controllers\Hrd\ReportController::class, 'index'])->name('hrd.reports');
    Route::get('/employees', [\App\Http\Controllers\Hrd\ReportController::class, 'employees'])->name('hrd.employees');
    Route::get('/employees/{user}/history', [\App\Http\Controllers\Hrd\ReportController::class, 'employeeHistory'])->name('hrd.employees.history');
});
