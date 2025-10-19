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

        // === Base kegiatan query (filter role + tahun) ===
        $kegiatanBase = Kegiatan::query()
            ->with(['bidang', 'user'])
            ->where('tahun', $year);

        if ($user->hasRole('staf')) {
            $kegiatanBase->where('user_id', $user->id)
                         ->where('bidang_id', $user->bidang_id);
        } elseif ($user->hasRole('kabid') && $user->bidang_id) {
            $kegiatanBase->where('bidang_id', $user->bidang_id);
        }
        // admin: tanpa filter (lihat semua)

        // Ambil id set
        $kegiatanIds     = (clone $kegiatanBase)->pluck('id');
        $subBase         = SubKegiatan::whereIn('kegiatan_id', $kegiatanIds);
        $subIds          = (clone $subBase)->pluck('id');
        $rincianBase     = RincianKegiatan::whereIn('sub_kegiatan_id', $subIds);
        $rincianIds      = (clone $rincianBase)->pluck('id');
        $realRincianBase = RealisasiRincian::whereIn('rincian_kegiatan_id', $rincianIds)
                                ->whereYear('tanggal_realisasi', $year);

        // === Kartu angka ringkas ===
        $totalKegiatan     = (clone $kegiatanBase)->count();
        $totalSubKegiatan  = (clone $subBase)->count();
        $totalRincian      = (clone $rincianBase)->count();
        $totalPagu         = (clone $rincianBase)->sum('anggaran'); // pagu = total rincian
        $totalRealisasi    = (clone $realRincianBase)->sum('realisasi_anggaran');
        $persentaseRealisasi = $totalPagu > 0 ? ($totalRealisasi / $totalPagu) * 100 : 0;

        // === Progress per Sub & per Kegiatan ===
        // Pagu per Sub
        $paguPerSub = RincianKegiatan::select('sub_kegiatan_id', DB::raw('SUM(anggaran) as pagu'))
            ->whereIn('sub_kegiatan_id', $subIds)
            ->groupBy('sub_kegiatan_id')
            ->get()
            ->keyBy('sub_kegiatan_id');

        // Realisasi per Sub
        $realPerSub = RealisasiRincian::select('rincian_kegiatans.sub_kegiatan_id', DB::raw('SUM(realisasi_rincians.realisasi_anggaran) as realisasi'))
            ->join('rincian_kegiatans', 'rincian_kegiatans.id', '=', 'realisasi_rincians.rincian_kegiatan_id')
            ->whereIn('rincian_kegiatans.sub_kegiatan_id', $subIds)
            ->whereYear('realisasi_rincians.tanggal_realisasi', $year)
            ->groupBy('rincian_kegiatans.sub_kegiatan_id')
            ->get()
            ->keyBy('sub_kegiatan_id');

        // Fisik per Sub (rata-rata realisasi_fisik yang terisi)
        $fisikPerSub = RealisasiRincian::select('rincian_kegiatans.sub_kegiatan_id', DB::raw('AVG(realisasi_rincians.realisasi_fisik) as avg_fisik'))
            ->join('rincian_kegiatans', 'rincian_kegiatans.id', '=', 'realisasi_rincians.rincian_kegiatan_id')
            ->whereIn('rincian_kegiatans.sub_kegiatan_id', $subIds)
            ->whereYear('realisasi_rincians.tanggal_realisasi', $year)
            ->whereNotNull('realisasi_rincians.realisasi_fisik')
            ->groupBy('rincian_kegiatans.sub_kegiatan_id')
            ->get()
            ->keyBy('sub_kegiatan_id');

        // Map Sub -> progress anggaran (%)
        $progressAnggaranSub = [];
        foreach ($subIds as $sid) {
            $pagu = (float)($paguPerSub[$sid]->pagu ?? 0);
            $real = (float)($realPerSub[$sid]->realisasi ?? 0);
            $progressAnggaranSub[$sid] = $pagu > 0 ? ($real / $pagu) * 100 : 0;
        }

        // Rata-rata fisik & anggaran (diambil rata-rata per sub)
        $avgProgressAnggaran = !empty($progressAnggaranSub)
            ? array_sum($progressAnggaranSub) / count($progressAnggaranSub)
            : 0;

        // untuk fisik, kalau kosong -> 0
        $valuesFisik = [];
        foreach ($subIds as $sid) {
            $valuesFisik[] = (float)($fisikPerSub[$sid]->avg_fisik ?? 0);
        }
        $avgProgressFisik = !empty($valuesFisik) ? array_sum($valuesFisik) / count($valuesFisik) : 0;

        // Progress per Kegiatan (dari sub-subnya)
        // Pagu per Kegiatan
        $paguPerKegiatan = RincianKegiatan::select('sub_kegiatans.kegiatan_id', DB::raw('SUM(rincian_kegiatans.anggaran) as pagu'))
            ->join('sub_kegiatans', 'sub_kegiatans.id', '=', 'rincian_kegiatans.sub_kegiatan_id')
            ->whereIn('sub_kegiatans.kegiatan_id', $kegiatanIds)
            ->groupBy('sub_kegiatans.kegiatan_id')
            ->get()
            ->keyBy('kegiatan_id');

        // Realisasi per Kegiatan
        $realPerKegiatan = RealisasiRincian::select('sub_kegiatans.kegiatan_id', DB::raw('SUM(realisasi_rincians.realisasi_anggaran) as realisasi'))
            ->join('rincian_kegiatans', 'rincian_kegiatans.id', '=', 'realisasi_rincians.rincian_kegiatan_id')
            ->join('sub_kegiatans', 'sub_kegiatans.id', '=', 'rincian_kegiatans.sub_kegiatan_id')
            ->whereIn('sub_kegiatans.kegiatan_id', $kegiatanIds)
            ->whereYear('realisasi_rincians.tanggal_realisasi', $year)
            ->groupBy('sub_kegiatans.kegiatan_id')
            ->get()
            ->keyBy('kegiatan_id');

        // On Track (progress anggaran kegiatan >= 80%)
        $kegiatanOnTrack = 0;
        foreach ($kegiatanIds as $kid) {
            $pagu = (float)($paguPerKegiatan[$kid]->pagu ?? 0);
            $real = (float)($realPerKegiatan[$kid]->realisasi ?? 0);
            $pct  = $pagu > 0 ? ($real / $pagu) * 100 : 0;
            if ($pct >= 80) $kegiatanOnTrack++;
        }

        // === Per Bidang (ringkas) ===
        // list bidang yang relevan (dari kegiatan yang terlihat)
        $bidangIds = (clone $kegiatanBase)->select('bidang_id')->distinct()->pluck('bidang_id');
        $bidangRows = Bidang::whereIn('id', $bidangIds)->get();

        // total kegiatan per bidang
        $kegCountPerBidang = (clone $kegiatanBase)
            ->select('bidang_id', DB::raw('COUNT(*) as jml'))
            ->groupBy('bidang_id')->get()->keyBy('bidang_id');

        // avg progress anggaran per bidang (hitung dari kegiatan2 di bidang tsb)
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

        // siapkan array presentasi per bidang
        $bidangs = $bidangRows->map(function ($b) use ($kegCountPerBidang, $avgProgPerBidang) {
            return [
                'id'             => $b->id,
                'nama'           => $b->nama,
                'total_kegiatan' => (int)($kegCountPerBidang[$b->id]->jml ?? 0),
                'avg_progress'   => (float)($avgProgPerBidang[$b->id] ?? 0),
                'icon'           => 'fas fa-layer-group', // sesuaikan ikon jika perlu
            ];
        });

        // === Kegiatan terbaru + atribut turunan ===
        $kegiatanTerbaru = (clone $kegiatanBase)
            ->latest()
            ->take(8)
            ->get();

        foreach ($kegiatanTerbaru as $k) {
            $kid = $k->id;
            $k->target_anggaran            = (float)($paguPerKegiatan[$kid]->pagu ?? 0);
            $k->current_budget_realization = (float)($realPerKegiatan[$kid]->realisasi ?? 0);

            // progress fisik (rata2 sub) untuk kegiatan ini
            $subsOfK = SubKegiatan::where('kegiatan_id', $kid)->pluck('id');
            if ($subsOfK->isNotEmpty()) {
                $fisikRows = RealisasiRincian::select('rincian_kegiatans.sub_kegiatan_id', DB::raw('AVG(realisasi_rincians.realisasi_fisik) as avg_fisik'))
                    ->join('rincian_kegiatans', 'rincian_kegiatans.id', '=', 'realisasi_rincians.rincian_kegiatan_id')
                    ->whereIn('rincian_kegiatans.sub_kegiatan_id', $subsOfK)
                    ->whereYear('realisasi_rincians.tanggal_realisasi', $year)
                    ->whereNotNull('realisasi_rincians.realisasi_fisik')
                    ->groupBy('rincian_kegiatans.sub_kegiatan_id')
                    ->pluck('avg_fisik')
                    ->all();

                $k->current_progress = !empty($fisikRows)
                    ? array_sum($fisikRows) / count($fisikRows)
                    : 0.0;
            } else {
                $k->current_progress = 0.0;
            }
        }

        return view('dashboard.index', [
            'totalKegiatan'        => $totalKegiatan,
            'totalSubKegiatan'     => $totalSubKegiatan,
            'totalRincian'         => $totalRincian,
            'avgProgressFisik'     => $avgProgressFisik,
            'avgProgressAnggaran'  => $avgProgressAnggaran,
            'kegiatanOnTrack'      => $kegiatanOnTrack,
            'totalPagu'            => $totalPagu,
            'totalRealisasi'       => $totalRealisasi,
            'persentaseRealisasi'  => $persentaseRealisasi,
            'bidangs'              => $bidangs,
            'kegiatanTerbaru'      => $kegiatanTerbaru,
        ]);
    }
}
