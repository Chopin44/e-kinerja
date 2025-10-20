<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\RealisasiRincian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class LaporanController extends Controller
{
    /** Halaman utama laporan */
    public function index()
    {
        $bidangs = Bidang::active()->get();
        return view('laporan.index', compact('bidangs'));
    }

    /** Generate laporan (AJAX) untuk preview */
    public function generate(Request $request)
    {
        $request->validate([
            'jenis_laporan' => 'required|in:bulanan,triwulan,tahunan,kinerja_bidang',
            'periode'       => 'required',
            'bidang_id'     => 'nullable|exists:bidangs,id',
            'triwulan'      => 'nullable|in:1,2,3,4',
        ]);

        $user     = Auth::user();
        $jenis    = $request->jenis_laporan;
        $periode  = Carbon::parse($request->periode);
        $bidangId = $request->bidang_id ? (int)$request->bidang_id : null;
        $tw       = $request->triwulan ? (int)$request->triwulan : null;

        // Scope sesuai role
        if ($user->hasRole('staf')) {
            $scope = ['role' => 'staf', 'bidang_id' => $user->bidang_id, 'user_id' => $user->id];
        } elseif ($user->hasRole('kabid')) {
            $scope = ['role' => 'kabid', 'bidang_id' => $user->bidang_id];
        } else {
            $scope = ['role' => 'admin', 'bidang_id' => $bidangId];
        }

        // Label "Untuk"
        $laporanUntuk = $user->hasRole('admin')
            ? optional(Bidang::find($bidangId))->nama ?? 'Seluruh Bidang'
            : $user->bidang->nama ?? 'Bidang Terkait';

        // Range tanggal + label
        [$startDate, $endDate, $label] = $this->resolvePeriodRange($jenis, $periode, $tw);

        // Ambil kegiatan sesuai scope
        $kegiatans = $this->scopedKegiatanQuery($scope, $startDate->year)->get();

        // Susun hierarki + hitung agregat periode
        $rows = $this->buildHierarchyForPeriod($kegiatans, $startDate, $endDate);

        // Ringkasan
        $summary = $this->buildSummary($rows);

        // Data final
        $data = [
            'label'     => $label,
            'ringkasan' => $summary,
            'kegiatans' => $rows,
        ];

        // Ambil penandatangan = user ber-Role admin (biasanya Kepala Dinas)
        $penandatangan = User::role('admin')
            ->when(method_exists(User::class, 'scopeActive'), fn($q) => $q->active())
            ->orderByRaw("CASE WHEN LOWER(name) LIKE '%kepala%' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->first() ?? $user;

        return response()->json([
            'success' => true,
            'html'    => view('laporan.preview', [
                'data'          => $data,
                'jenis_laporan' => $jenis,
                'periode'       => $periode,
                'user'          => $user,
                'laporanUntuk'  => $laporanUntuk,
                'penandatangan' => $penandatangan,
            ])->render(),
        ]);
    }

    /** Ekspor Excel */
    public function exportExcel(Request $request)
    {
        return Excel::download(new \App\Exports\LaporanExport($request->all()), 'laporan_opd.xlsx');
    }

    /** Ekspor PDF – gunakan data yang sama dengan preview */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'jenis_laporan' => 'required|in:bulanan,triwulan,tahunan,kinerja_bidang',
            'periode'       => 'required',
            'bidang_id'     => 'nullable|exists:bidangs,id',
            'triwulan'      => 'nullable|in:1,2,3,4',
        ]);

        $user     = Auth::user();
        $jenis    = $request->jenis_laporan;
        $periode  = Carbon::parse($request->periode);
        $bidangId = $request->bidang_id ? (int)$request->bidang_id : null;
        $tw       = $request->triwulan ? (int)$request->triwulan : null;

        // Scope (sama kaya generate)
        if ($user->hasRole('staf')) {
            $scope = ['role' => 'staf', 'bidang_id' => $user->bidang_id, 'user_id' => $user->id];
        } elseif ($user->hasRole('kabid')) {
            $scope = ['role' => 'kabid', 'bidang_id' => $user->bidang_id];
        } else {
            $scope = ['role' => 'admin', 'bidang_id' => $bidangId];
        }

        // Untuk (header)
        if ($user->hasRole('admin')) {
            $laporanUntuk = optional(Bidang::find($bidangId))->nama ?? 'Seluruh Bidang';
        } else {
            $laporanUntuk = $user->bidang->nama ?? 'Bidang Terkait';
        }

        // Range & label (sama)
        [$startDate, $endDate, $label] = $this->resolvePeriodRange($jenis, $periode, $tw);

        // Data (sama)
        $kegiatans = $this->scopedKegiatanQuery($scope, $startDate->year)->get();
        $rows      = $this->buildHierarchyForPeriod($kegiatans, $startDate, $endDate);
        $summary   = $this->buildSummary($rows);

        $data = [
            'label'     => $label,
            'ringkasan' => $summary,
            'kegiatans' => $rows,
        ];

        // Penandatangan (admin; fallback user login)
        $penandatangan = User::role('admin')
            ->when(method_exists(User::class, 'scopeActive'), fn($q) => $q->active())
            ->orderByRaw("CASE WHEN LOWER(name) LIKE '%kepala%' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->first() ?? $user;

        // KIRIM KE VIEW KHUSUS PDF DENGAN CSS INLINE
        $pdf = \PDF::setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ])
            ->loadView('laporan.pdf', [
                'data'          => $data,
                'jenis_laporan' => $jenis,
                'periode'       => $periode,
                'user'          => $user,
                'laporanUntuk'  => $laporanUntuk,
                'penandatangan' => $penandatangan,
            ])
            ->setPaper('a4', 'landscape'); // <— supaya mirip tampilan print landscape

        return $pdf->download('laporan_opd.pdf');
    }

    /* ======================= Helpers ======================= */

    /** Tentukan range tanggal & label periode */
    private function resolvePeriodRange(string $jenis, Carbon $periode, ?int $triwulan = null): array
    {
        switch ($jenis) {
            case 'bulanan':
                $start = $periode->copy()->startOfMonth();
                $end   = $periode->copy()->endOfMonth();
                $label = $periode->translatedFormat('F Y');
                break;

            case 'triwulan':
                $q = $triwulan ?: (int)ceil($periode->month / 3);
                $startMonth = ($q - 1) * 3 + 1;
                $endMonth   = $startMonth + 2;
                $start      = Carbon::createFromDate($periode->year, $startMonth, 1)->startOfDay();
                $end        = Carbon::createFromDate($periode->year, $endMonth, 1)->endOfMonth()->endOfDay();
                $map        = [1 => 'Triwulan I (Jan–Mar)', 2 => 'Triwulan II (Apr–Jun)', 3 => 'Triwulan III (Jul–Sep)', 4 => 'Triwulan IV (Okt–Des)'];
                $label      = ($map[$q] ?? 'Triwulan') . ' ' . $periode->year;
                break;

            case 'tahunan':
                $start = Carbon::createFromDate($periode->year, 1, 1)->startOfDay();
                $end   = Carbon::createFromDate($periode->year, 12, 31)->endOfDay();
                $label = (string)$periode->year;
                break;

            default: // kinerja_bidang
                $start = Carbon::createFromDate($periode->year, 1, 1)->startOfDay();
                $end   = Carbon::createFromDate($periode->year, 12, 31)->endOfDay();
                $label = 'Tahun ' . $periode->year;
                break;
        }
        return [$start, $end, $label];
    }

    /** Query kegiatan sesuai scope role + tahun */
    private function scopedKegiatanQuery(array $scope, int $year)
    {
        $q = Kegiatan::with([
            'bidang',
            'subKegiatans.rincianKegiatans',
        ]);

        if (method_exists(Kegiatan::class, 'byTahun')) {
            $q->byTahun($year);
        } else {
            $q->where(function ($qq) use ($year) {
                $qq->whereYear('tanggal_mulai', $year)
                   ->orWhereYear('tanggal_selesai', $year)
                   ->orWhere('tahun', $year);
            });
        }

        if (($scope['role'] ?? null) === 'staf') {
            $q->where('user_id', $scope['user_id'] ?? null)
              ->where('bidang_id', $scope['bidang_id'] ?? null);
        } elseif (($scope['role'] ?? null) === 'kabid') {
            $q->where('bidang_id', $scope['bidang_id'] ?? null);
        } elseif (!empty($scope['bidang_id'])) {
            $q->where('bidang_id', $scope['bidang_id']);
        }

        return $q;
    }

    /**
     * Bangun hierarki Kegiatan → Sub → Rincian (realisasi dalam periode).
     * Deviasi di SEMUA level dalam PERSEN:
     *   deviasi_pct = (realisasi - pagu) / pagu * 100 (jika pagu > 0)
     */
private function buildHierarchyForPeriod($kegiatans, Carbon $startDate, Carbon $endDate)
{
    $allRincianIds = [];
    foreach ($kegiatans as $k) {
        foreach ($k->subKegiatans as $s) {
            foreach ($s->rincianKegiatans as $r) {
                $allRincianIds[] = $r->id;
            }
        }
    }
    $allRincianIds = array_values(array_unique($allRincianIds));

    $rr = collect();
    if (!empty($allRincianIds)) {
        $rr = RealisasiRincian::select(
                'rincian_kegiatan_id',
                \DB::raw('SUM(COALESCE(realisasi_anggaran,0)) as total_anggaran'),
                \DB::raw('AVG(COALESCE(realisasi_fisik,0)) as avg_fisik')
            )
            ->whereIn('rincian_kegiatan_id', $allRincianIds)
            ->whereBetween('tanggal_realisasi', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('rincian_kegiatan_id')
            ->get()
            ->keyBy('rincian_kegiatan_id');
    }

    $num = fn($v) => (float) ($v ?? 0);
    $kOut = [];

    foreach ($kegiatans as $k) {
        $paguK = 0;
        foreach ($k->subKegiatans as $s) {
            $paguK += $num($s->target_anggaran);
        }
        if (!$paguK) $paguK = $num($k->target_anggaran);

        $subOut = [];
        $realisasiK = 0;
        $fisikValsK = [];

        foreach ($k->subKegiatans as $s) {
            $paguS = $num($s->target_anggaran);
            $rinciOut = [];
            $realS = 0;
            $fisikValsS = [];

            foreach ($s->rincianKegiatans as $r) {
                $angR   = $num($r->anggaran);
                $row    = $rr->get($r->id);
                $realR  = $row ? $num($row->total_anggaran) : 0.0;
                $fisikR = $row ? (float) $row->avg_fisik : null;

                $sisaR       = max($angR - $realR, 0);
                $deviasiPctR = $angR > 0 ? (($realR - $angR) / $angR) * 100 : null;

                $rinciOut[] = [
                    'id'          => $r->id,
                    'uraian'      => $r->uraian,
                    'anggaran'    => $angR,
                    'realisasi'   => $realR,
                    'sisa'        => $sisaR,
                    'deviasi_pct' => is_null($deviasiPctR) ? null : round($deviasiPctR, 1),
                    'fisik'       => $fisikR,
                ];

                $realS += $realR;
                if (!is_null($fisikR)) $fisikValsS[] = $fisikR;
            }

            $avgFisikS = count($fisikValsS) ? array_sum($fisikValsS) / count($fisikValsS) : 0;
            $sisaS       = max($paguS - $realS, 0);
            $progressS   = $paguS > 0 ? ($realS / $paguS) * 100 : 0;
            $deviasiPctS = $paguS > 0 ? (($realS - $paguS) / $paguS) * 100 : null;

            $subOut[] = [
                'id'              => $s->id,
                'nama'            => $s->nama,
                'pagu'            => $paguS,
                'realisasi'       => $realS,
                'sisa'            => $sisaS,
                'progress'        => round($progressS, 1),
                'deviasi_pct'     => is_null($deviasiPctS) ? null : round($deviasiPctS, 1),
                'target_fisik'    => 100, // misal target 100% default
                'realisasi_fisik' => round($avgFisikS, 1),
                'rincians'        => $rinciOut,
            ];

            $realisasiK += $realS;
            if ($avgFisikS > 0) $fisikValsK[] = $avgFisikS;
        }

        $avgFisikK = count($fisikValsK) ? array_sum($fisikValsK) / count($fisikValsK) : 0;
        $sisaK       = max($paguK - $realisasiK, 0);
        $progressK   = $paguK > 0 ? ($realisasiK / $paguK) * 100 : 0;
        $deviasiPctK = $paguK > 0 ? (($realisasiK - $paguK) / $paguK) * 100 : null;

        $kOut[] = [
            'id'              => $k->id,
            'bidang'          => $k->bidang->nama ?? '-',
            'nama'            => $k->nama,
            'pagu'            => $paguK,
            'realisasi'       => $realisasiK,
            'sisa'            => $sisaK,
            'progress'        => round($progressK, 1),
            'deviasi_pct'     => is_null($deviasiPctK) ? null : round($deviasiPctK, 1),
            'target_fisik'    => 100, // default 100%
            'realisasi_fisik' => round($avgFisikK, 1),
            'subkegiatans'    => $subOut,
        ];
    }

    return $kOut;
}


    /** Ringkasan agregat dari rows */
    private function buildSummary(array $rows): array
    {
        $totalPagu      = 0;
        $totalRealisasi = 0;
        $fisikVals      = [];

        foreach ($rows as $k) {
            $totalPagu      += (float)$k['pagu'];
            $totalRealisasi += (float)$k['realisasi'];
            foreach ($k['subkegiatans'] as $s) {
                foreach ($s['rincians'] as $r) {
                    if (!is_null($r['fisik'])) $fisikVals[] = (float)$r['fisik'];
                }
            }
        }

        $persen   = $totalPagu > 0 ? ($totalRealisasi / $totalPagu) * 100 : 0;
        $avgFisik = count($fisikVals) ? array_sum($fisikVals) / count($fisikVals) : 0;

        return [
            'total_kegiatan'  => count($rows),
            'total_pagu'      => $totalPagu,
            'total_realisasi' => $totalRealisasi,
            'persentase'      => round($persen, 1),
            'rata_fisik'      => round($avgFisik, 1),
        ];
    }
}
