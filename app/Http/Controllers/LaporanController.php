<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Kegiatan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class LaporanController extends Controller
{
    /**
     * Halaman utama laporan
     */
    public function index()
    {
        $bidangs = Bidang::active()->get();
        return view('laporan.index', compact('bidangs'));
    }

    /**
     * Generate laporan sesuai jenis (bulanan, triwulan, tahunan, kinerja bidang)
     */
    public function generate(Request $request)
    {
        $request->validate([
            'jenis_laporan' => 'required|in:bulanan,triwulan,tahunan,kinerja_bidang',
            'periode' => 'required',
            'bidang_id' => 'nullable|exists:bidangs,id',
        ]);

        $bidangId = $request->bidang_id;
        $jenis = $request->jenis_laporan;
        $periode = Carbon::parse($request->periode);
        $triwulan = $request->triwulan;

        switch ($jenis) {
            case 'bulanan':
                $data = $this->laporanBulanan($bidangId, $periode);
                break;

            case 'triwulan':
                $data = $this->laporanTriwulan($bidangId, $periode, $triwulan);
                break;

            case 'tahunan':
                $data = $this->laporanTahunan($bidangId, $periode);
                break;

            default:
                $data = $this->laporanKinerjaBidang($bidangId, $periode);
                break;
        }

        return response()->json([
            'success' => true,
            'html' => view('laporan.preview', [
                'data' => $data,
                'jenis_laporan' => $jenis,
                'periode' => $periode,
            ])->render(),
        ]);
    }

    // =====================================================
    // ========== 1. LAPORAN BULANAN =======================
    // =====================================================
    private function laporanBulanan($bidangId, Carbon $periode)
    {
        $query = Kegiatan::with(['bidang', 'realisasis' => function ($q) use ($periode) {
            $q->whereMonth('tanggal_realisasi', $periode->month)
              ->whereYear('tanggal_realisasi', $periode->year);
        }])->byTahun($periode->year);

        if ($bidangId) $query->byBidang($bidangId);
        $kegiatans = $query->get();

        $result = $kegiatans->map(function ($k) {
            return [
                'nama' => $k->nama,
                'bidang' => $k->bidang->nama ?? '-',
                'fisik' => round($k->realisasis->avg('realisasi_fisik') ?? 0, 1),
                'anggaran' => $k->realisasis->sum('realisasi_anggaran') ?? 0,
                'status' => ucfirst($k->status ?? '-'),
            ];
        });

        return ['bulanan' => $result];
    }

    // =====================================================
    // ========== 2. LAPORAN TRIWULAN ======================
    // =====================================================
 private function laporanTriwulan($bidangId, Carbon $periode, $manualQuarter = null)
{
    $year = $periode->year;
    $quarter = $manualQuarter ? (int) $manualQuarter : ceil($periode->month / 3);

    $labelMap = [
        1 => 'Triwulan I (Jan–Mar)',
        2 => 'Triwulan II (Apr–Jun)',
        3 => 'Triwulan III (Jul–Sep)',
        4 => 'Triwulan IV (Okt–Des)',
    ];

    $label = $labelMap[$quarter] ?? 'Triwulan Tidak Diketahui';
    $startMonth = ($quarter - 1) * 3 + 1;
    $endMonth = $startMonth + 2;

    // 🔹 Ambil kegiatan berdasarkan tanggal_kegiatan / tanggal_mulai (bukan tanggal realisasi)
    $query = Kegiatan::with(['bidang', 'realisasis'])
        ->whereYear('tanggal_mulai', $year)
        ->whereBetween(\DB::raw('MONTH(tanggal_mulai)'), [$startMonth, $endMonth])
        ->byTahun($year);

    if ($bidangId) $query->byBidang($bidangId);

    $kegiatans = $query->get();

    // 🔹 Ringkasan
    $totalAnggaran = $kegiatans->sum('target_anggaran');
    $realisasiAnggaran = $kegiatans->sum(fn($k) => $k->realisasis->sum('realisasi_anggaran'));
    $rataFisik = $kegiatans->avg(fn($k) => $k->realisasis->avg('realisasi_fisik')) ?? 0;
    $persentase = $totalAnggaran > 0 ? ($realisasiAnggaran / $totalAnggaran) * 100 : 0;

    // 🔹 Detail per kegiatan
    $daftarKegiatan = $kegiatans->map(function ($k) {
        $realisasi = $k->realisasis->sum('realisasi_anggaran');
        $sisa = $k->target_anggaran - $realisasi;
        $deviasi = $realisasi - $k->target_anggaran;

        return [
            'bidang' => $k->bidang->nama ?? '-',
            'nama' => $k->nama,
            'anggaran' => $k->target_anggaran,
            'realisasi' => $realisasi,
            'sisa' => $sisa,
            'deviasi' => $deviasi,
        ];
    });

    return [
        'label' => $label,
        'quarter' => $quarter,
        'ringkasan' => [
            'total' => $kegiatans->count(),
            'rata_fisik' => round($rataFisik, 1),
            'anggaran' => $realisasiAnggaran,
            'persentase' => round($persentase, 1),
        ],
        'kegiatans' => $daftarKegiatan,
    ];
}



    // =====================================================
    // ========== 3. LAPORAN TAHUNAN =======================
    // =====================================================
    private function laporanTahunan($bidangId, Carbon $periode)
    {
        $year = $periode->year;

        $query = Kegiatan::with(['bidang', 'realisasis' => function ($q) use ($year) {
            $q->whereYear('tanggal_realisasi', $year);
        }])->byTahun($year);

        if ($bidangId) $query->byBidang($bidangId);
        $kegiatans = $query->get();

        $result = collect(range(1, 12))->map(function ($month) use ($kegiatans, $year) {
            $namaBulan = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
            $bulanData = $kegiatans->map(function ($k) use ($month) {
                $r = $k->realisasis->filter(fn($r) => Carbon::parse($r->tanggal_realisasi)->month === $month);
                return [
                    'fisik' => $r->avg('realisasi_fisik') ?? 0,
                    'anggaran' => $r->sum('realisasi_anggaran') ?? 0,
                ];
            });

            $rataFisik = $bulanData->avg('fisik');
            $realisasi = $bulanData->sum('anggaran');
            $target = $kegiatans->sum('target_anggaran');
            $persentase = $target > 0 ? ($realisasi / $target) * 100 : 0;

            return [
                'nama_bulan' => $namaBulan,
                'total_kegiatan' => $kegiatans->count(),
                'rata_fisik' => round($rataFisik, 1),
                'realisasi_anggaran' => $realisasi,
                'persentase' => round($persentase, 1),
            ];
        });

        return ['tahunan' => $result];
    }

    // =====================================================
    // ========== 4. LAPORAN KINERJA BIDANG ================
    // =====================================================
    private function laporanKinerjaBidang($bidangId, Carbon $periode)
    {
        $query = Kegiatan::with(['bidang', 'realisasis'])->byTahun($periode->year);
        if ($bidangId) $query->byBidang($bidangId);
        $kegiatans = $query->get();

        $summary = [
            'total_kegiatan' => $kegiatans->count(),
            'selesai' => $kegiatans->where('status', 'selesai')->count(),
            'dalam_progress' => $kegiatans->where('status', 'aktif')->count(),
            'terlambat' => $kegiatans->where('status_evaluasi', 'terlambat')->count(),
            'total_anggaran' => $kegiatans->sum('target_anggaran'),
            'realisasi_anggaran' => $kegiatans->sum('current_budget_realization'),
        ];

        $bidangs = Bidang::active();
        if ($bidangId) $bidangs->where('id', $bidangId);

        $perBidang = $bidangs->get()->map(function ($bidang) use ($periode) {
            $kegiatanBidang = $bidang->kegiatans()->byTahun($periode->year)->get();
            $total = $kegiatanBidang->sum('target_anggaran');
            $realisasi = $kegiatanBidang->sum('current_budget_realization');

            return [
                'nama' => $bidang->nama,
                'total_kegiatan' => $kegiatanBidang->count(),
                'avg_capaian' => round($kegiatanBidang->avg('current_progress'), 1),
                'total_anggaran' => $total,
                'realisasi_anggaran' => $realisasi,
                'persentase_anggaran' => $total > 0 ? ($realisasi / $total) * 100 : 0,
                'deviasi' => $realisasi - $total,
            ];
        });

        return compact('summary', 'perBidang');
    }

    // =====================================================
    // ========== 5. EXPORT LAPORAN ========================
    // =====================================================
    public function exportExcel(Request $request)
    {
        return Excel::download(new \App\Exports\LaporanExport($request->all()), 'laporan_opd.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $jenis = $request->jenis_laporan ?? 'kinerja_bidang';
        $periode = Carbon::parse($request->periode ?? now());
        $bidangId = $request->bidang_id;
        $triwulan = $request->triwulan;

        $data = match ($jenis) {
            'bulanan' => $this->laporanBulanan($bidangId, $periode),
            'triwulan' => $this->laporanTriwulan($bidangId, $periode, $triwulan),
            'tahunan' => $this->laporanTahunan($bidangId, $periode),
            default => $this->laporanKinerjaBidang($bidangId, $periode),
        };

        $pdf = PDF::loadView('laporan.pdf', compact('data', 'jenis', 'periode'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan_opd.pdf');
    }
}
