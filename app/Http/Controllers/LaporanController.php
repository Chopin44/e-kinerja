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
    public function index()
    {
        $bidangs = Bidang::active()->get();
        return view('laporan.index', compact('bidangs'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'bidang_id' => 'nullable|exists:bidangs,id',
            'jenis_laporan' => 'required|in:bulanan,triwulan,tahunan,realisasi_fisik,realisasi_anggaran,kinerja_bidang',
            'periode' => 'required|date',
        ]);

        $bidangId = $request->bidang_id;
        $jenisLaporan = $request->jenis_laporan;
        $periode = Carbon::parse($request->periode);

        // Tentukan jenis laporan
        switch ($jenisLaporan) {
            case 'bulanan':
                $data = $this->generateLaporanBulanan($bidangId, $periode);
                break;
            case 'triwulan':
                $data = $this->generateLaporanTriwulan($bidangId, $periode);
                break;
            case 'tahunan':
                $data = $this->generateLaporanTahunan($bidangId, $periode);
                break;
            case 'kinerja_bidang':
            default:
                $data = $this->generateLaporanKinerjaBidang($bidangId, $periode);
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'html' => view('laporan.preview', compact('data', 'jenisLaporan', 'periode'))->render()
        ]);
    }

    /**
     * ===== LAPORAN KINERJA PER BIDANG =====
     */
    private function generateLaporanKinerjaBidang($bidangId, $periode)
    {
        // 🔹 Filter kegiatan sesuai tahun dan bidang
        $query = Kegiatan::with(['bidang', 'realisasis'])->byTahun($periode->year);

        if ($bidangId) {
            $query->byBidang($bidangId);
        }

        $kegiatans = $query->get();

        // 🔹 Summary utama
        $summary = [
            'total_kegiatan'      => $kegiatans->count(),
            'selesai'             => $kegiatans->where('status', 'selesai')->count(),
            'dalam_progress'      => $kegiatans->where('status', 'aktif')->count(),
            'terlambat'           => $kegiatans->filter(fn($k) => $k->status_evaluasi === 'terlambat')->count(),
            'total_anggaran'      => $kegiatans->sum('target_anggaran'),
            'realisasi_anggaran'  => $kegiatans->sum('current_budget_realization'),
        ];

        // 🔹 Ambil bidang (semua atau satu)
        $bidangs = Bidang::active();
        if ($bidangId) {
            $bidangs->where('id', $bidangId);
        }

        // 🔹 Hitung data per bidang
        $perBidang = $bidangs->get()->map(function ($bidang) use ($periode) {
            $kegiatanBidang = $bidang->kegiatans()->byTahun($periode->year)->get();

            $totalAnggaran = $kegiatanBidang->sum('target_anggaran');
            $realisasiAnggaran = $kegiatanBidang->sum('current_budget_realization');

            return [
                'nama'                 => $bidang->nama,
                'total_kegiatan'       => $kegiatanBidang->count(),
                'avg_capaian'          => $kegiatanBidang->avg('current_progress'),
                'total_anggaran'       => $totalAnggaran,
                'realisasi_anggaran'   => $realisasiAnggaran,
                'persentase_anggaran'  => $totalAnggaran > 0 ? ($realisasiAnggaran / $totalAnggaran) * 100 : 0,
                'deviasi'              => $realisasiAnggaran - $totalAnggaran,
                'deviasi_persen'       => $totalAnggaran > 0
                                            ? (($realisasiAnggaran - $totalAnggaran) / $totalAnggaran) * 100
                                            : 0,
            ];
        });

        return [
            'summary'    => $summary,
            'per_bidang' => $perBidang,
            'kegiatans'  => $kegiatans,
        ];
    }

    /**
     * ===== EXPORT EXCEL =====
     */
    public function exportExcel(Request $request)
    {
        // Pastikan sudah ada LaporanExport
        return Excel::download(new \App\Exports\LaporanExport($request->all()), 'laporan_opd.xlsx');
    }

    /**
     * ===== EXPORT PDF =====
     */
    public function exportPdf(Request $request)
    {
        $data = $this->generate($request)->getData();

        $pdf = PDF::loadView('laporan.pdf', compact('data'));
        return $pdf->download('laporan_opd.pdf');
    }
}
