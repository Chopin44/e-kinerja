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

        /**
         * ========================================
         *  DASHBOARD UNTUK STAF
         * ========================================
         */
        if ($user->hasRole('staf')) {
            // Ambil kegiatan milik staf sesuai bidang & tahun
            $kegiatanIds = Kegiatan::where('user_id', $user->id)
                ->where('bidang_id', $user->bidang_id)
                ->where('tahun', $year)
                ->pluck('id');

            // Ambil semua subkegiatan milik kegiatan tersebut
            $subIds = SubKegiatan::whereIn('kegiatan_id', $kegiatanIds)->pluck('id');

            // Ambil rincian berdasarkan subkegiatan
            $rincianIds = RincianKegiatan::whereIn('sub_kegiatan_id', $subIds)->pluck('id');

            // Total anggaran (pagu)
            $totalPagu = RincianKegiatan::whereIn('id', $rincianIds)->sum('anggaran');

            // Total realisasi (toleransi tanggal_realisasi null)
            $totalRealisasi = RealisasiRincian::whereIn('rincian_kegiatan_id', $rincianIds)
                ->where(function ($q) use ($year) {
                    $q->whereYear('tanggal_realisasi', $year)
                      ->orWhereNull('tanggal_realisasi');
                })
                ->sum('realisasi_anggaran');

            // Rata-rata fisik
            $avgFisik = RealisasiRincian::whereIn('rincian_kegiatan_id', $rincianIds)
                ->where(function ($q) use ($year) {
                    $q->whereYear('tanggal_realisasi', $year)
                      ->orWhereNull('tanggal_realisasi');
                })
                ->whereNotNull('realisasi_fisik')
                ->avg('realisasi_fisik') ?? 0;

            // Persentase realisasi
            $persentaseRealisasi = $totalPagu > 0 ? ($totalRealisasi / $totalPagu) * 100 : 0;

            // Kegiatan terbaru milik staf
            $kegiatanTerbaru = Kegiatan::where('user_id', $user->id)
                ->where('bidang_id', $user->bidang_id)
                ->where('tahun', $year)
                ->with(['bidang', 'subKegiatans.rincianKegiatans.realisasiRincians'])
                ->latest()
                ->take(8)
                ->get();

            // Hitung progress tiap kegiatan
            foreach ($kegiatanTerbaru as $k) {
                $subIds = $k->subKegiatans->pluck('id');
                $rincianIds = RincianKegiatan::whereIn('sub_kegiatan_id', $subIds)->pluck('id');

                $target = RincianKegiatan::whereIn('sub_kegiatan_id', $subIds)->sum('anggaran');

                $realisasi = RealisasiRincian::whereIn('rincian_kegiatan_id', $rincianIds)
                    ->where(function ($q) use ($year) {
                        $q->whereYear('tanggal_realisasi', $year)
                          ->orWhereNull('tanggal_realisasi');
                    })
                    ->sum('realisasi_anggaran');

                $fisik = RealisasiRincian::whereIn('rincian_kegiatan_id', $rincianIds)
                    ->where(function ($q) use ($year) {
                        $q->whereYear('tanggal_realisasi', $year)
                          ->orWhereNull('tanggal_realisasi');
                    })
                    ->whereNotNull('realisasi_fisik')
                    ->avg('realisasi_fisik');

                $k->target_anggaran = $target;
                $k->current_budget_realization = $realisasi;
                $k->current_progress = round($fisik ?? 0, 1);
            }

            return view('dashboard.staff', [
                'user'                => $user,
                'tahun'               => $year,
                'totalPagu'           => round($totalPagu ?? 0, 0),
                'totalRealisasi'      => round($totalRealisasi ?? 0, 0),
                'persentaseRealisasi' => round($persentaseRealisasi, 1),
                'avgFisik'            => round($avgFisik ?? 0, 1),
                'kegiatanTerbaru'     => $kegiatanTerbaru,
            ]);
        }

        /**
         * ========================================
         *  DASHBOARD UNTUK ADMIN / KABID
         * ========================================
         */
        $kegiatanBase = Kegiatan::query()
            ->with(['bidang', 'user'])
            ->where('tahun', $year);

        // Jika kabid, filter berdasarkan bidang
        if ($user->hasRole('kabid') && $user->bidang_id) {
            $kegiatanBase->where('bidang_id', $user->bidang_id);
        }

        // Ambil semua ID yang relevan
        $kegiatanIds = (clone $kegiatanBase)->pluck('id');
        $subIds = SubKegiatan::whereIn('kegiatan_id', $kegiatanIds)->pluck('id');
        $rincianIds = RincianKegiatan::whereIn('sub_kegiatan_id', $subIds)->pluck('id');

        $realRincianBase = RealisasiRincian::whereIn('rincian_kegiatan_id', $rincianIds)
            ->where(function ($q) use ($year) {
                $q->whereYear('tanggal_realisasi', $year)
                  ->orWhereNull('tanggal_realisasi');
            });

        // Statistik umum
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

        // Rata-rata progress anggaran per subkegiatan
        $paguPerSub = RincianKegiatan::select('sub_kegiatan_id', DB::raw('SUM(anggaran) as pagu'))
            ->whereIn('sub_kegiatan_id', $subIds)
            ->groupBy('sub_kegiatan_id')
            ->get()
            ->keyBy('sub_kegiatan_id');

        $realPerSub = RealisasiRincian::select('rincian_kegiatans.sub_kegiatan_id', DB::raw('SUM(realisasi_rincians.realisasi_anggaran) as realisasi'))
            ->join('rincian_kegiatans', 'rincian_kegiatans.id', '=', 'realisasi_rincians.rincian_kegiatan_id')
            ->whereIn('rincian_kegiatans.sub_kegiatan_id', $subIds)
            ->where(function ($q) use ($year) {
                $q->whereYear('realisasi_rincians.tanggal_realisasi', $year)
                  ->orWhereNull('realisasi_rincians.tanggal_realisasi');
            })
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

        // Kegiatan On Track (>= 80%)
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
            ->where(function ($q) use ($year) {
                $q->whereYear('realisasi_rincians.tanggal_realisasi', $year)
                  ->orWhereNull('realisasi_rincians.tanggal_realisasi');
            })
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

        // Statistik per bidang
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
                ->where(function ($q) use ($year) {
                    $q->whereYear('realisasi_rincians.tanggal_realisasi', $year)
                      ->orWhereNull('realisasi_rincians.tanggal_realisasi');
                })
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

        // Kegiatan terbaru (Admin / Kabid)
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
                    ->where(function ($q) use ($year) {
                        $q->whereYear('realisasi_rincians.tanggal_realisasi', $year)
                          ->orWhereNull('realisasi_rincians.tanggal_realisasi');
                    })
                    ->whereNotNull('realisasi_rincians.realisasi_fisik')
                    ->avg('realisasi_rincians.realisasi_fisik');
                $k->current_progress = round($fisikRows ?? 0, 1);
            } else {
                $k->current_progress = 0.0;
            }
        }

        // Kirim ke view dashboard utama
        return view('dashboard.index', [
            'totalKegiatan' => $totalKegiatan,
            'totalSubKegiatan' => $totalSubKegiatan,
            'totalRincian' => $totalRincian,
            'avgProgressFisik' => round($avgProgressFisik, 1),
            'avgProgressAnggaran' => round($avgProgressAnggaran, 1),
            'kegiatanOnTrack' => $kegiatanOnTrack,
            'totalPagu' => round($totalPagu ?? 0, 0),
            'totalRealisasi' => round($totalRealisasi ?? 0, 0),
            'persentaseRealisasi' => round($persentaseRealisasi, 1),
            'bidangs' => $bidangs,
            'kegiatanTerbaru' => $kegiatanTerbaru,
        ]);
    }
}
