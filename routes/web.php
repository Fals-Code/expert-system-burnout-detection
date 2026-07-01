<?php

use App\Http\Controllers\Admin\KnowledgeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DeteksiController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\Hrd\ReportController;
use App\Http\Controllers\HrdController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/locale/{lang}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:critical');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Shared Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::get('/help', [HelpController::class, 'index'])->name('help');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::get('/notifications/{notification}/read-redirect', [NotificationController::class, 'readAndRedirect'])->name('notifications.read_redirect');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Karyawan Routes
Route::middleware(['auth', 'role:karyawan'])->prefix('karyawan')->group(function () {
    Route::get('/dashboard', [KaryawanController::class, 'index'])->name('karyawan.dashboard');
    Route::get('/deteksi/intro', [DeteksiController::class, 'intro'])->name('karyawan.deteksi.intro');
    Route::get('/deteksi', [DeteksiController::class, 'index'])->name('karyawan.deteksi');
    Route::post('/deteksi', [DeteksiController::class, 'next'])->name('karyawan.deteksi.next');
    Route::post('/deteksi/save', [DeteksiController::class, 'saveSession'])->name('karyawan.deteksi.save');
    Route::post('/deteksi/resume', [DeteksiController::class, 'resumeSession'])->name('karyawan.deteksi.resume');
    Route::get('/hasil', [DeteksiController::class, 'showResult'])->name('karyawan.hasil');
    Route::get('/hasil/download/{id}', [DeteksiController::class, 'downloadReport'])->name('karyawan.laporan.download');
    Route::get('/history', [KaryawanController::class, 'history'])->name('karyawan.history');
    Route::get('/deteksi/reset', [DeteksiController::class, 'reset'])->name('karyawan.deteksi.reset');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/logs', [AdminController::class, 'logs'])->name('admin.logs');

    // Knowledge Base
    Route::get('/knowledge', [KnowledgeController::class, 'index'])->name('admin.knowledge');
    Route::get('/knowledge/backup', [KnowledgeController::class, 'backupKnowledgeBase'])->name('admin.knowledge.backup');
    Route::post('/knowledge/restore', [KnowledgeController::class, 'restoreKnowledgeBase'])->name('admin.knowledge.restore')->middleware('throttle:critical');

    Route::post('/knowledge/gejala', [KnowledgeController::class, 'storeGejala'])->name('admin.knowledge.gejala.store');
    Route::put('/knowledge/gejala/{gejala}', [KnowledgeController::class, 'updateGejala'])->name('admin.knowledge.gejala.update');
    Route::delete('/knowledge/gejala/{gejala}', [KnowledgeController::class, 'destroyGejala'])->name('admin.knowledge.gejala.destroy');

    Route::post('/knowledge/aturan', [KnowledgeController::class, 'storeAturan'])->name('admin.knowledge.aturan.store');
    Route::put('/knowledge/aturan/{aturan}', [KnowledgeController::class, 'updateAturan'])->name('admin.knowledge.aturan.update');
    Route::put('/knowledge/aturan/{aturan}/quick', [KnowledgeController::class, 'quickUpdateAturan'])->name('admin.knowledge.aturan.quick');
    Route::delete('/knowledge/aturan/{aturan}', [KnowledgeController::class, 'destroyAturan'])->name('admin.knowledge.aturan.destroy');

    Route::post('/knowledge/diagnosa', [KnowledgeController::class, 'storeDiagnosa'])->name('admin.knowledge.diagnosa.store');
    Route::put('/knowledge/diagnosa/{diagnosa}', [KnowledgeController::class, 'updateDiagnosa'])->name('admin.knowledge.diagnosa.update');
    Route::delete('/knowledge/diagnosa/{diagnosa}', [KnowledgeController::class, 'destroyDiagnosa'])->name('admin.knowledge.diagnosa.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});

// HRD Routes
Route::middleware(['auth', 'role:hrd'])->prefix('hrd')->group(function () {
    Route::get('/dashboard', [HrdController::class, 'index'])->name('hrd.dashboard');
    Route::get('/reports', [ReportController::class, 'index'])->name('hrd.reports');
    Route::get('/employees', [ReportController::class, 'employees'])->name('hrd.employees');
    Route::get('/employees/{user}/history', [ReportController::class, 'employeeHistory'])->name('hrd.employees.history');
});
