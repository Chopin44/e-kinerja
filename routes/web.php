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
    | Semua route di dalam group ini hanya bisa diakses user dengan role "admin"
    */
    Route::middleware(['role:admin'])->group(function () {
        // Manajemen User
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');

        // Manajemen Evaluasi (opsional, jika ingin CRUD penuh)
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
    | REALISASI
    |--------------------------------------------------------------------------
    */
    Route::resource('realisasi', RealisasiController::class);
    Route::post('realisasi/{realisasi}/upload-dokumen', [RealisasiController::class, 'uploadDokumen'])
        ->name('realisasi.upload-dokumen');
    Route::delete('dokumen/{dokumen}', [RealisasiController::class, 'deleteDokumen'])
        ->name('dokumen.delete');

    // Dokumen - preview inline, download, dan download-all ZIP
    Route::get('realisasi/{realisasi}/preview/{dokumen}', [RealisasiController::class, 'preview'])
        ->name('realisasi.preview');
    Route::get('realisasi/{realisasi}/download/{dokumen}', [RealisasiController::class, 'download'])
        ->name('realisasi.download');
    Route::get('realisasi/{realisasi}/download-all', [RealisasiController::class, 'downloadAll'])
        ->name('realisasi.download-all');

    /*
    |--------------------------------------------------------------------------
    | MONITORING & EVALUASI
    |--------------------------------------------------------------------------
    */
    Route::resource('monitoring', MonitoringController::class)
        ->parameters(['monitoring' => 'kegiatan'])
        ->except(['create', 'store']);

    // Form Evaluasi pada halaman Monitoring — hanya untuk Admin
    Route::post('monitoring/{kegiatan}/evaluasi', [MonitoringController::class, 'storeEvaluasi'])
        ->middleware('role:admin')
        ->name('monitoring.evaluasi');

    // Statistik dan Grafik
    Route::get('monitoring-stats', [MonitoringController::class, 'getStats'])->name('monitoring.stats');
    Route::get('monitoring/{kegiatan}/progress-chart', [MonitoringController::class, 'getProgressChart'])
        ->name('monitoring.progress-chart');

    // Export Monitoring (Excel/PDF/CSV)
    Route::get('monitoring-export', [MonitoringController::class, 'exportMonitoring'])
        ->name('monitoring.export');

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
        Route::get('kegiatan-by-bidang/{bidang}', [KegiatanController::class, 'getByBidang'])
            ->name('kegiatan-by-bidang');
        Route::get('dashboard-stats', [DashboardController::class, 'getStats'])
            ->name('dashboard-stats');
    });
});

require __DIR__.'/auth.php';
