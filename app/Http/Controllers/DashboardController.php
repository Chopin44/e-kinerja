<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Realisasi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $currentYear = Carbon::now()->year;

        // === Statistik umum ===
        $kegiatans = Kegiatan::byTahun($currentYear)->with('realisasis')->get();

        $totalKegiatan       = $kegiatans->count();
        $avgProgressFisik    = $kegiatans->avg('current_progress');
        $avgProgressAnggaran = $this->getAvgBudgetProgress($kegiatans);
        $kegiatanOnTrack     = $kegiatans->where('status_evaluasi', 'on_track')->count();

        // === Data total anggaran untuk Pie Chart ===
        $totalPagu       = $kegiatans->sum('target_anggaran');
        $totalRealisasi  = $kegiatans->sum(fn($k) => $k->realisasis->sum('realisasi_anggaran'));
        $persentaseRealisasi = $totalPagu > 0 ? ($totalRealisasi / $totalPagu) * 100 : 0;

        // === Data per bidang ===
        $bidangs = Bidang::active()
            ->with(['kegiatans' => function ($query) use ($currentYear) {
                $query->byTahun($currentYear);
            }])
            ->get()
            ->map(function ($bidang) {
                $totalKegiatan = $bidang->kegiatans->count();
                $avgProgress   = $bidang->kegiatans->avg('current_progress');

                return [
                    'nama'            => $bidang->nama,
                    'icon'            => $bidang->icon,
                    'total_kegiatan'  => $totalKegiatan,
                    'avg_progress'    => round($avgProgress, 1),
                ];
            });

        // === Kegiatan terbaru ===
        $kegiatanTerbaru = Kegiatan::byTahun($currentYear)
            ->with(['bidang', 'user', 'realisasis'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalKegiatan',
            'avgProgressFisik',
            'avgProgressAnggaran',
            'kegiatanOnTrack',
            'bidangs',
            'kegiatanTerbaru',
            'totalPagu',
            'totalRealisasi',
            'persentaseRealisasi'
        ));
    }

    /**
     * Hitung rata-rata capaian anggaran dari seluruh kegiatan
     */
    private function getAvgBudgetProgress($kegiatans)
    {
        if ($kegiatans->isEmpty()) return 0;

        $totalProgress = $kegiatans->sum(function ($kegiatan) {
            $latestRealisasi = $kegiatan->realisasis->last();
            if (!$latestRealisasi || $kegiatan->target_anggaran == 0) {
                return 0;
            }
            return ($latestRealisasi->realisasi_anggaran / $kegiatan->target_anggaran) * 100;
        });

        return round($totalProgress / $kegiatans->count(), 1);
    }
}
