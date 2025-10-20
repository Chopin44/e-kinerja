<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\RincianKegiatan;
use App\Models\RealisasiRincian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = (int)($request->get('tahun') ?: date('Y'));
        $user = Auth::user();

        // Jika user adalah STAF → tampilkan dashboard sederhana
        if ($user->hasRole('staf')) {
            // Ambil ringkasan milik staf
            $kegiatanIds = Kegiatan::where('user_id', $user->id)
                ->where('bidang_id', $user->bidang_id)
                ->where('tahun', $year)
                ->pluck('id');

            $kegiatanTerbaru = Kegiatan::where('user_id', $user->id)
                ->where('bidang_id', $user->bidang_id)
                ->where('tahun', $year)
                ->latest()
                ->take(8)
                ->get();


            $subIds = SubKegiatan::whereIn('kegiatan_id', $kegiatanIds)->pluck('id');
            $rincianIds = RincianKegiatan::whereIn('sub_kegiatan_id', $subIds)->pluck('id');

            $totalPagu = RincianKegiatan::whereIn('id', $rincianIds)->sum('anggaran');
            $totalRealisasi = RealisasiRincian::whereIn('rincian_kegiatan_id', $rincianIds)
                ->whereYear('tanggal_realisasi', $year)
                ->sum('realisasi_anggaran');

            $avgFisik = RealisasiRincian::whereIn('rincian_kegiatan_id', $rincianIds)
                ->whereYear('tanggal_realisasi', $year)
                ->whereNotNull('realisasi_fisik')
                ->avg('realisasi_fisik');

            $persentaseRealisasi = $totalPagu > 0 ? ($totalRealisasi / $totalPagu) * 100 : 0;

            return view('dashboard.staff', [
                'user'                => $user,
                'tahun'               => $year,
                'totalPagu'           => $totalPagu,
                'totalRealisasi'      => $totalRealisasi,
                'persentaseRealisasi' => round($persentaseRealisasi, 1),
                'avgFisik'            => round($avgFisik ?? 0, 1),
                'kegiatanTerbaru' => $kegiatanTerbaru,
            ]);
        }

        // === Dashboard utama untuk Admin & Kabid ===
        $kegiatanBase = Kegiatan::query()
            ->with(['bidang', 'user'])
            ->where('tahun', $year);

        if ($user->hasRole('kabid') && $user->bidang_id) {
            $kegiatanBase->where('bidang_id', $user->bidang_id);
        }

        // Ambil id set
        $kegiatanIds = (clone $kegiatanBase)->pluck('id');
        $subIds = SubKegiatan::whereIn('kegiatan_id', $kegiatanIds)->pluck('id');
        $rincianIds = RincianKegiatan::whereIn('sub_kegiatan_id', $subIds)->pluck('id');
        $realRincianBase = RealisasiRincian::whereIn('rincian_kegiatan_id', $rincianIds)
            ->whereYear('tanggal_realisasi', $year);

        // === Ringkasan umum ===
        $totalKegiatan = (clone $kegiatanBase)->count();
        $totalSubKegiatan = SubKegiatan::whereIn('kegiatan_id', $kegiatanIds)->count();
        $totalRincian = RincianKegiatan::whereIn('sub_kegiatan_id', $subIds)->count();
        $totalPagu = RincianKegiatan::whereIn('id', $rincianIds)->sum('anggaran');
        $totalRealisasi = (clone $realRincianBase)->sum('realisasi_anggaran');
        $persentaseRealisasi = $totalPagu > 0 ? ($totalRealisasi / $totalPagu) * 100 : 0;

        // Fisik rata-rata
        $avgProgressFisik = (clone $realRincianBase)
            ->whereNotNull('realisasi_fisik')
            ->avg('realisasi_fisik') ?? 0;

        // Anggaran rata-rata per sub
        $paguPerSub = RincianKegiatan::select('sub_kegiatan_id', DB::raw('SUM(anggaran) as pagu'))
            ->whereIn('sub_kegiatan_id', $subIds)
            ->groupBy('sub_kegiatan_id')
            ->get()
            ->keyBy('sub_kegiatan_id');

        $realPerSub = RealisasiRincian::select('rincian_kegiatans.sub_kegiatan_id', DB::raw('SUM(realisasi_rincians.realisasi_anggaran) as realisasi'))
            ->join('rincian_kegiatans', 'rincian_kegiatans.id', '=', 'realisasi_rincians.rincian_kegiatan_id')
            ->whereIn('rincian_kegiatans.sub_kegiatan_id', $subIds)
            ->whereYear('realisasi_rincians.tanggal_realisasi', $year)
            ->groupBy('rincian_kegiatans.sub_kegiatan_id')
            ->get()
            ->keyBy('sub_kegiatan_id');

        $progressAnggaranSub = [];
        foreach ($subIds as $sid) {
            $pagu = (float)($paguPerSub[$sid]->pagu ?? 0);
            $real = (float)($realPerSub[$sid]->realisasi ?? 0);
            $progressAnggaranSub[$sid] = $pagu > 0 ? ($real / $pagu) * 100 : 0;
        }
        $avgProgressAnggaran = !empty($progressAnggaranSub)
            ? array_sum($progressAnggaranSub) / count($progressAnggaranSub)
            : 0;

        // On Track (progress >= 80%)
        $paguPerKegiatan = RincianKegiatan::select('sub_kegiatans.kegiatan_id', DB::raw('SUM(rincian_kegiatans.anggaran) as pagu'))
            ->join('sub_kegiatans', 'sub_kegiatans.id', '=', 'rincian_kegiatans.sub_kegiatan_id')
            ->whereIn('sub_kegiatans.kegiatan_id', $kegiatanIds)
            ->groupBy('sub_kegiatans.kegiatan_id')
            ->get()
            ->keyBy('kegiatan_id');

        $realPerKegiatan = RealisasiRincian::select('sub_kegiatans.kegiatan_id', DB::raw('SUM(realisasi_rincians.realisasi_anggaran) as realisasi'))
            ->join('rincian_kegiatans', 'rincian_kegiatans.id', '=', 'realisasi_rincians.rincian_kegiatan_id')
            ->join('sub_kegiatans', 'sub_kegiatans.id', '=', 'rincian_kegiatans.sub_kegiatan_id')
            ->whereIn('sub_kegiatans.kegiatan_id', $kegiatanIds)
            ->whereYear('realisasi_rincians.tanggal_realisasi', $year)
            ->groupBy('sub_kegiatans.kegiatan_id')
            ->get()
            ->keyBy('kegiatan_id');

        $kegiatanOnTrack = 0;
        foreach ($kegiatanIds as $kid) {
            $pagu = (float)($paguPerKegiatan[$kid]->pagu ?? 0);
            $real = (float)($realPerKegiatan[$kid]->realisasi ?? 0);
            $pct = $pagu > 0 ? ($real / $pagu) * 100 : 0;
            if ($pct >= 80) $kegiatanOnTrack++;
        }

        // === Per Bidang ===
        $bidangIds = (clone $kegiatanBase)->select('bidang_id')->distinct()->pluck('bidang_id');
        $bidangRows = Bidang::whereIn('id', $bidangIds)->get();

        $kegCountPerBidang = (clone $kegiatanBase)
            ->select('bidang_id', DB::raw('COUNT(*) as jml'))
            ->groupBy('bidang_id')
            ->get()
            ->keyBy('bidang_id');

        $avgProgPerBidang = [];
        foreach ($bidangIds as $bid) {
            $kegs = (clone $kegiatanBase)->where('bidang_id', $bid)->pluck('id');
            $paguBid = RincianKegiatan::select(DB::raw('SUM(rincian_kegiatans.anggaran) as pagu'))
                ->join('sub_kegiatans', 'sub_kegiatans.id', '=', 'rincian_kegiatans.sub_kegiatan_id')
                ->whereIn('sub_kegiatans.kegiatan_id', $kegs)
                ->value('pagu') ?? 0;

            $realBid = RealisasiRincian::select(DB::raw('SUM(realisasi_rincians.realisasi_anggaran) as realisasi'))
                ->join('rincian_kegiatans', 'rincian_kegiatans.id', '=', 'realisasi_rincians.rincian_kegiatan_id')
                ->join('sub_kegiatans', 'sub_kegiatans.id', '=', 'rincian_kegiatans.sub_kegiatan_id')
                ->whereIn('sub_kegiatans.kegiatan_id', $kegs)
                ->whereYear('realisasi_rincians.tanggal_realisasi', $year)
                ->value('realisasi') ?? 0;

            $avgProgPerBidang[$bid] = $paguBid > 0 ? ($realBid / $paguBid) * 100 : 0;
        }

        $bidangs = $bidangRows->map(function ($b) use ($kegCountPerBidang, $avgProgPerBidang) {
            return [
                'id' => $b->id,
                'nama' => $b->nama,
                'total_kegiatan' => (int)($kegCountPerBidang[$b->id]->jml ?? 0),
                'avg_progress' => (float)($avgProgPerBidang[$b->id] ?? 0),
                'icon' => 'fas fa-layer-group',
            ];
        });

        // === Kegiatan terbaru ===
        $kegiatanTerbaru = (clone $kegiatanBase)
            ->latest()
            ->take(8)
            ->get();

        foreach ($kegiatanTerbaru as $k) {
            $kid = $k->id;
            $k->target_anggaran = (float)($paguPerKegiatan[$kid]->pagu ?? 0);
            $k->current_budget_realization = (float)($realPerKegiatan[$kid]->realisasi ?? 0);

            $subsOfK = SubKegiatan::where('kegiatan_id', $kid)->pluck('id');
            if ($subsOfK->isNotEmpty()) {
                $fisikRows = RealisasiRincian::select(DB::raw('AVG(realisasi_rincians.realisasi_fisik) as avg_fisik'))
                    ->join('rincian_kegiatans', 'rincian_kegiatans.id', '=', 'realisasi_rincians.rincian_kegiatan_id')
                    ->whereIn('rincian_kegiatans.sub_kegiatan_id', $subsOfK)
                    ->whereYear('realisasi_rincians.tanggal_realisasi', $year)
                    ->whereNotNull('realisasi_rincians.realisasi_fisik')
                    ->avg('realisasi_rincians.realisasi_fisik');
                $k->current_progress = round($fisikRows ?? 0, 1);
            } else {
                $k->current_progress = 0.0;
            }
        }

        return view('dashboard.index', [
            'totalKegiatan' => $totalKegiatan,
            'totalSubKegiatan' => $totalSubKegiatan,
            'totalRincian' => $totalRincian,
            'avgProgressFisik' => $avgProgressFisik,
            'avgProgressAnggaran' => $avgProgressAnggaran,
            'kegiatanOnTrack' => $kegiatanOnTrack,
            'totalPagu' => $totalPagu,
            'totalRealisasi' => $totalRealisasi,
            'persentaseRealisasi' => $persentaseRealisasi,
            'bidangs' => $bidangs,
            'kegiatanTerbaru' => $kegiatanTerbaru,
        ]);
    }
}
