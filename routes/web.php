<?php
// routes/web.php

use Illuminate\Support\Facades\Route;

// Controller imports
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\RealisasiController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\EvaluasiController;

// Tambahan: controller Sub & Rincian
use App\Http\Controllers\SubKegiatanController;
use App\Http\Controllers\RincianKegiatanController;
use App\Http\Controllers\RealisasiRincianController;

// ✅ Tambahan baru: DokumenController
use App\Http\Controllers\DokumenController;

Route::get('/', fn () => redirect('/login'));

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | ADMIN AREA (Role: admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::resource('evaluasi', EvaluasiController::class)->except(['show']);
    });

    /*
    |--------------------------------------------------------------------------
    | KEGIATAN
    |--------------------------------------------------------------------------
    */
    Route::resource('kegiatan', KegiatanController::class);

    /*
    |--------------------------------------------------------------------------
    | SUBKEGIATAN & RINCIAN
    |--------------------------------------------------------------------------
    */
    Route::resource('subkegiatan', SubKegiatanController::class)
        ->only(['create','store','show','edit','update','destroy'])
        ->middleware(['role:admin|kabid|pimpinan|staf']);

    Route::resource('subkegiatan.rincian', RincianKegiatanController::class)
        ->shallow()
        ->only(['index','create','store','edit','update','destroy'])
        ->middleware(['role:admin|kabid|pimpinan|staf']);

    /*
    |--------------------------------------------------------------------------
    | REALISASI
    |--------------------------------------------------------------------------
    */
    Route::resource('realisasi', RealisasiController::class);

    Route::resource('rincian.realisasi-rincian', RealisasiRincianController::class)
        ->shallow()
        ->only(['create','store','edit','update','destroy'])
        ->middleware(['role:admin|kabid|pimpinan|staf']);

    /*
    |--------------------------------------------------------------------------
    | DOKUMEN REALISASI (Controller terpisah)
    |--------------------------------------------------------------------------
    | - Upload: POST /realisasi/{realisasi}/dokumen
    | - Hapus : DELETE /dokumen/{dokumen}
    | - Bisa tambahkan preview/download di masa depan
    */
    Route::post('realisasi/{realisasi}/dokumen', [DokumenController::class, 'store'])
        ->name('dokumen.store')
        ->middleware(['role:admin|kabid|pimpinan|staf']);

    Route::delete('dokumen/{dokumen}', [DokumenController::class, 'destroy'])
        ->name('dokumen.destroy')
        ->middleware(['role:admin|kabid|pimpinan|staf']);

    /*
    |--------------------------------------------------------------------------
    | MONITORING & EVALUASI
    |--------------------------------------------------------------------------
    */
    Route::resource('monitoring', MonitoringController::class)
        ->parameters(['monitoring' => 'kegiatan'])
        ->except(['create', 'store']);

    Route::post('monitoring/{kegiatan}/evaluasi', [MonitoringController::class, 'storeEvaluasi'])
        ->middleware('role:admin')
        ->name('monitoring.evaluasi');

    Route::get('monitoring-stats', [MonitoringController::class, 'getStats'])->name('monitoring.stats');
    Route::get('monitoring/{kegiatan}/progress-chart', [MonitoringController::class, 'getProgressChart'])->name('monitoring.progress-chart');
    Route::get('monitoring-export', [MonitoringController::class, 'exportMonitoring'])->name('monitoring.export');

    /*
    |--------------------------------------------------------------------------
    | LAPORAN
    |--------------------------------------------------------------------------
    */
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::post('/laporan/generate', [LaporanController::class, 'generate'])->name('laporan.generate');
    Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
    Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');

    /*
    |--------------------------------------------------------------------------
    | API (AJAX HELPERS)
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('kegiatan-by-bidang/{bidang}', [KegiatanController::class, 'getByBidang'])->name('kegiatan-by-bidang');
        Route::get('dashboard-stats', [DashboardController::class, 'getStats'])->name('dashboard-stats');
    });
});

require __DIR__.'/auth.php';
