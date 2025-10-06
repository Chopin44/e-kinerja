<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Realisasi;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $currentYear = Carbon::now()->year;
        
        // Statistik umum
        $totalKegiatan = Kegiatan::byTahun($currentYear)->count();
        $avgProgressFisik = Kegiatan::byTahun($currentYear)->get()->avg('current_progress');
        $avgProgressAnggaran = $this->getAvgBudgetProgress($currentYear);
        $kegiatanOnTrack = Kegiatan::byTahun($currentYear)->get()
            ->where('status_evaluasi', 'on_track')->count();
        
        // Data per bidang
        $bidangs = Bidang::active()->with(['kegiatans' => function($query) use ($currentYear) {
            $query->byTahun($currentYear);
        }])->get()->map(function($bidang) {
            return [
                'nama' => $bidang->nama,
                'icon' => $bidang->icon,
                'total_kegiatan' => $bidang->total_kegiatan,
                'avg_progress' => $bidang->avg_progress
            ];
        });
        
        // Kegiatan terbaru
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
            'kegiatanTerbaru'
        ));
    }
    
    private function getAvgBudgetProgress($year)
    {
        $kegiatans = Kegiatan::byTahun($year)->with('realisasis')->get();
        
        if ($kegiatans->isEmpty()) return 0;
        
        $totalProgress = $kegiatans->sum(function ($kegiatan) {
            $latestRealisasi = $kegiatan->realisasis->last();
            if (!$latestRealisasi || $kegiatan->target_anggaran == 0) return 0;
            
            return ($latestRealisasi->realisasi_anggaran / $kegiatan->target_anggaran) * 100;
        });
        
        return $totalProgress / $kegiatans->count();
    }
}