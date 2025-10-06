<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Evaluasi;
use App\Models\Kegiatan;
use App\Models\Realisasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = $request->get('tahun', Carbon::now()->year);

        $query = Kegiatan::with(['bidang', 'user', 'realisasis', 'evaluasis'])
            ->byTahun($currentYear);

        // Filter by role: staf hanya bisa lihat bidangnya
        if (Auth::user()->role === 'staf') {
            $query->byBidang(Auth::user()->bidang_id);
        }

        // Filters
        if ($request->filled('bidang_id')) {
            $query->byBidang($request->bidang_id);
        }
        if ($request->filled('periode')) {
            // Disederhanakan: field 'periode' di tabel kegiatan berisi Q1..Q4
            $query->where('periode', $request->periode);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status); // on_track/late/problem/aktif/selesai
        }

        $kegiatans = $query->paginate(10);

        // Stats (untuk kartu di atas)
        $stats = $this->calculateStats($currentYear, $request->get('bidang_id'));
        $totalKegiatan = $stats['total_kegiatan'] ?? 0;
        $onTrack       = $stats['on_track'] ?? 0;
        $late          = $stats['terlambat'] ?? 0;
        $problem       = $stats['bermasalah'] ?? 0;

        $bidangs = Bidang::active()->get();

        return view('monitoring.index', compact(
            'kegiatans',
            'bidangs',
            'totalKegiatan',
            'onTrack',
            'late',
            'problem'
        ));
    }

    public function show(Kegiatan $kegiatan)
    {
        // Staf hanya boleh lihat kegiatan di bidangnya
        if (Auth::user()->role === 'staf' && Auth::user()->bidang_id !== $kegiatan->bidang_id) {
            abort(403, 'Unauthorized access');
        }

        $kegiatan->load([
            'bidang',
            'user',
            'realisasis' => fn($q) => $q->orderBy('tanggal_realisasi', 'desc'),
            'realisasis.dokumens',
            'evaluasis' => fn($q) => $q->orderBy('tanggal_evaluasi', 'desc'),
            'evaluasis.evaluator'
        ]);

        $timeline          = $this->generateProgressTimeline($kegiatan);
        $latestEvaluation  = $kegiatan->evaluasis->first();
        $budget_percentage = $kegiatan->target_anggaran > 0
            ? round(($kegiatan->current_budget_realization / $kegiatan->target_anggaran) * 100, 2)
            : 0;

        return view('monitoring.show', [
            'kegiatan'           => $kegiatan,
            'timeline'           => $timeline,
            'latestEvaluation'   => $latestEvaluation,
            'status_evaluasi'    => $kegiatan->status_evaluasi,
            'current_progress'   => $kegiatan->current_progress,
            'budget_realization' => $kegiatan->current_budget_realization,
            'budget_percentage'  => $budget_percentage,
        ]);
    }

    public function edit(Kegiatan $kegiatan)
    {
        if (Auth::user()->role === 'staf' && Auth::user()->bidang_id !== $kegiatan->bidang_id) {
            abort(403, 'Unauthorized access');
        }

        $bidangs = Bidang::active()->get();
        return view('monitoring.edit', compact('kegiatan', 'bidangs'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        if (Auth::user()->role === 'staf' && Auth::user()->bidang_id !== $kegiatan->bidang_id) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'nama'            => 'required|string|max:255',
            'bidang_id'       => ['required', Rule::exists('bidangs', 'id')],
            'periode'         => ['nullable', Rule::in(['Q1','Q2','Q3','Q4'])],
            'target_fisik'    => 'nullable|numeric|min:0|max:100',
            'target_anggaran' => 'nullable|numeric|min:0',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'          => ['nullable', Rule::in(['on_track','late','problem','aktif','selesai'])],
            'deskripsi'       => 'nullable|string',
        ]);

        $kegiatan->update([
            'nama'             => $request->nama,
            'bidang_id'        => $request->bidang_id,
            'periode'          => $request->periode,
            'target_fisik'     => $request->target_fisik,
            'target_anggaran'  => $request->target_anggaran,
            'tanggal_mulai'    => $request->tanggal_mulai,
            'tanggal_selesai'  => $request->tanggal_selesai,
            'status'           => $request->status,
            'deskripsi'        => $request->deskripsi,
        ]);

        return redirect()->route('monitoring.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        if (Auth::user()->role === 'staf' && Auth::user()->bidang_id !== $kegiatan->bidang_id) {
            abort(403, 'Unauthorized access');
        }

        $kegiatan->delete();
        return redirect()->route('monitoring.index')->with('success', 'Kegiatan berhasil dihapus.');
    }

    /**
     * Simpan Evaluasi — KHUSUS ADMIN
     */
    public function storeEvaluasi(Request $request, Kegiatan $kegiatan)
    {
        $request->validate([
            'status_evaluasi'  => 'required|in:on_track,terlambat,tidak_sesuai',
            'catatan_evaluasi' => 'required|string|min:10',
            'rekomendasi'      => 'nullable|string',
            'tanggal_evaluasi' => 'required|date',
        ]);

        if (Auth::user()->role !== 'admin') {
            // bila HTML form
            return redirect()
                ->route('monitoring.show', $kegiatan)
                ->with('error', 'Hanya admin yang dapat melakukan evaluasi.');
        }

        try {
            DB::beginTransaction();

            $evaluasi = Evaluasi::create([
                'kegiatan_id'      => $kegiatan->id,
                'evaluator_id'     => Auth::id(),
                'status_evaluasi'  => $request->status_evaluasi,
                'catatan_evaluasi' => $request->catatan_evaluasi,
                'rekomendasi'      => $request->rekomendasi,
                'tanggal_evaluasi' => $request->tanggal_evaluasi,
            ]);

            DB::commit();

            return redirect()
                ->route('monitoring.show', $kegiatan)
                ->with('success', 'Evaluasi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('monitoring.show', $kegiatan)
                ->with('error', 'Terjadi kesalahan saat menyimpan evaluasi: '.$e->getMessage());
        }
    }

    public function getStats(Request $request)
    {
        $tahun    = $request->get('tahun', Carbon::now()->year);
        $bidangId = $request->get('bidang_id');
        $stats    = $this->calculateStats($tahun, $bidangId);

        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function exportMonitoring(Request $request)
    {
        $tahun    = $request->get('tahun', Carbon::now()->year);
        $bidangId = $request->get('bidang_id');
        $format   = $request->get('format', 'excel'); // excel, pdf, csv

        $query = Kegiatan::with(['bidang', 'user', 'realisasis', 'evaluasis'])
            ->byTahun($tahun);

        if ($bidangId) $query->byBidang($bidangId);
        if (Auth::user()->role === 'staf') $query->byBidang(Auth::user()->bidang_id);

        $kegiatans = $query->get();

        switch ($format) {
            case 'pdf':  return $this->exportToPdf($kegiatans, $tahun);
            case 'csv':  return $this->exportToCsv($kegiatans, $tahun);
            default:     return $this->exportToExcel($kegiatans, $tahun);
        }
    }

    private function exportToExcel($kegiatans, $tahun)
    {
        return response()->json([
            'success' => true,
            'message' => 'Export Excel akan segera tersedia',
            'download_url' => '#'
        ]);
    }

    private function exportToPdf($kegiatans, $tahun)
    {
        return response()->json([
            'success' => true,
            'message' => 'Export PDF akan segera tersedia',
            'download_url' => '#'
        ]);
    }

    private function exportToCsv($kegiatans, $tahun)
    {
        $filename = "monitoring_kegiatan_{$tahun}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($kegiatans) {
            $file = fopen('php://output', 'w');

            // Header CSV
            fputcsv($file, [
                'Nama Kegiatan','Bidang','Penanggung Jawab',
                'Target Fisik (%)','Realisasi Fisik (%)',
                'Target Anggaran','Realisasi Anggaran',
                'Status Evaluasi','Tanggal Mulai','Tanggal Selesai'
            ]);

            // Data
            foreach ($kegiatans as $kegiatan) {
                fputcsv($file, [
                    $kegiatan->nama,
                    $kegiatan->bidang->nama ?? '-',
                    $kegiatan->user->name ?? '-',
                    $kegiatan->target_fisik,
                    $kegiatan->current_progress,
                    $kegiatan->target_anggaran,
                    $kegiatan->current_budget_realization,
                    ucfirst(str_replace('_', ' ', $kegiatan->status_evaluasi)),
                    optional($kegiatan->tanggal_mulai)->format('d/m/Y'),
                    optional($kegiatan->tanggal_selesai)->format('d/m/Y'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getBidangStats(Request $request, Bidang $bidang)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);

        $kegiatans = $bidang->kegiatans()->byTahun($tahun)->with('realisasis')->get();

        $stats = [
            'total_kegiatan'      => $kegiatans->count(),
            'selesai'             => $kegiatans->where('status','selesai')->count(),
            'dalam_progress'      => $kegiatans->where('status','aktif')->count(),
            'avg_progress_fisik'  => $kegiatans->avg('current_progress') ?: 0,
            'total_anggaran'      => $kegiatans->sum('target_anggaran'),
            'realisasi_anggaran'  => $kegiatans->sum('current_budget_realization'),
            'persentase_anggaran' => 0,
        ];

        if ($stats['total_anggaran'] > 0) {
            $stats['persentase_anggaran'] = ($stats['realisasi_anggaran'] / $stats['total_anggaran']) * 100;
        }

        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function getProgressChart(Request $request, Kegiatan $kegiatan)
    {
        $realisasis = $kegiatan->realisasis()->orderBy('tanggal_realisasi', 'asc')->get();

        $chartData = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Progress Fisik (%)',
                    'data'  => [],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Progress Anggaran (%)',
                    'data'  => [],
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4,
                ]
            ]
        ];

        foreach ($realisasis as $realisasi) {
            $chartData['labels'][] = $realisasi->tanggal_realisasi->format('M Y');
            $chartData['datasets'][0]['data'][] = (float)$realisasi->realisasi_fisik;

            $budgetPercentage = $kegiatan->target_anggaran > 0
                ? ($realisasi->realisasi_anggaran / $kegiatan->target_anggaran) * 100
                : 0;

            $chartData['datasets'][1]['data'][] = round($budgetPercentage, 2);
        }

        return response()->json(['success' => true, 'data' => $chartData]);
    }

    /* ----------------- Helpers ----------------- */

    private function calculateStats($tahun, $bidangId = null)
    {
        $query = Kegiatan::byTahun($tahun);
        if ($bidangId) $query->byBidang($bidangId);
        if (Auth::user()->role === 'staf') $query->byBidang(Auth::user()->bidang_id);

        $kegiatans = $query->with('realisasis', 'evaluasis')->get();

        $totalKegiatan = $kegiatans->count();
        $onTrack = 0; $terlambat = 0; $bermasalah = 0;

        foreach ($kegiatans as $kegiatan) {
            $statusEvaluasi = $kegiatan->status_evaluasi;

            switch ($statusEvaluasi) {
                case 'on_track':     $onTrack++; break;
                case 'terlambat':    $terlambat++; break;
                case 'tidak_sesuai': $bermasalah++; break;
                default:
                    $progress = $kegiatan->current_progress;
                    $target   = $kegiatan->target_fisik;
                    if ($target > 0) {
                        if ($progress >= $target * 0.9)      $onTrack++;
                        elseif ($progress >= $target * 0.7) $terlambat++;
                        else                                 $bermasalah++;
                    } else {
                        $bermasalah++;
                    }
            }
        }

        return [
            'total_kegiatan'       => $totalKegiatan,
            'on_track'             => $onTrack,
            'terlambat'            => $terlambat,
            'bermasalah'           => $bermasalah,
            'avg_progress'         => $kegiatans->avg('current_progress') ?: 0,
            'total_anggaran'       => $kegiatans->sum('target_anggaran'),
            'realisasi_anggaran'   => $kegiatans->sum('current_budget_realization'),
        ];
    }

    private function generateProgressTimeline($kegiatan)
    {
        $realisasis = $kegiatan->realisasis()->orderBy('tanggal_realisasi', 'asc')->get();

        $timeline = [];
        foreach ($realisasis as $realisasi) {
            $timeline[] = [
                'tanggal'            => $realisasi->tanggal_realisasi->format('d M Y'),
                'bulan'              => $realisasi->tanggal_realisasi->format('F Y'),
                'progress_fisik'     => (float)$realisasi->realisasi_fisik,
                'progress_anggaran'  => (float)$realisasi->realisasi_anggaran,
                'catatan'            => $realisasi->catatan,
                'lokasi'             => $realisasi->lokasi,
                'status'             => $realisasi->status,
            ];
        }
        return $timeline;
    }
}
