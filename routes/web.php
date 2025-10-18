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
    | ADMIN AREA (Role: admin) - menggunakan Spatie middleware: role:admin
    |--------------------------------------------------------------------------
    | Semua route di dalam group ini hanya bisa diakses user dengan role "admin".
    | Kalau kamu nanti mau kabid juga bisa kelola user atau evaluasi,
    | ubah jadi ->middleware(['role:admin|kabid'])
    */
    Route::middleware(['role:admin'])->group(function () {
        // Manajemen User
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggleActive'])
            ->name('users.toggle');

        // Manajemen Evaluasi (opsional, CRUD penuh kecuali show)
        Route::resource('evaluasi', EvaluasiController::class)->except(['show']);
    });

    /*
    |--------------------------------------------------------------------------
    | KEGIATAN
    |--------------------------------------------------------------------------
    | Resource penuh untuk Kegiatan (index, create, store, show, edit, update, destroy)
    */
    Route::resource('kegiatan', KegiatanController::class);

    /*
    |--------------------------------------------------------------------------
    | SUBKEGIATAN & RINCIAN
    |--------------------------------------------------------------------------
    | Dibuat nested supaya URL & route name cocok dengan view:
    |   - subkegiatan.* 
    |   - subkegiatan.rincian.*  (pakai ->shallow() agar edit/destroy jadi /rincian/{rincian})
    */
    Route::resource('subkegiatan', SubKegiatanController::class)
        ->only(['create','store','show','edit','update','destroy'])
        ->middleware(['role:admin|kabid|pimpinan|staf']);

    Route::resource('subkegiatan.rincian', RincianKegiatanController::class)
        ->shallow() // menghasilkan: rincian.edit/update/destroy tanpa prefix subkegiatan
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


    // Upload / hapus dokumen realisasi
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
    | - Semua user yang login bisa mengakses resource monitoring (lihat data).
    | - Simpan evaluasi di-monitoring hanya untuk admin.
    */
    Route::resource('monitoring', MonitoringController::class)
        ->parameters(['monitoring' => 'kegiatan'])
        ->except(['create', 'store']);

    Route::post('monitoring/{kegiatan}/evaluasi', [MonitoringController::class, 'storeEvaluasi'])
        ->middleware('role:admin')
        ->name('monitoring.evaluasi');

    // Statistik dan Grafik
    Route::get('monitoring-stats', [MonitoringController::class, 'getStats'])
        ->name('monitoring.stats');
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
