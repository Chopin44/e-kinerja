<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Dokumen;
use App\Models\Kegiatan;
use App\Models\Realisasi;
use App\Models\RealisasiRincian;
use App\Models\RincianKegiatan;
use App\Models\SubKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RealisasiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Realisasi::with([
            'kegiatan.bidang',
            'subKegiatan',
            'user',
            'realisasiRincians.rincianKegiatan:id,uraian,kategori'
        ])
        ->withCount(['realisasiRincians as rincian_count'])
        ->withSum('realisasiRincians as rincian_total_anggaran', 'realisasi_anggaran');

        if ($user->hasRole('staf')) {
            $query->whereHas('kegiatan', fn($q) => 
                $q->where('bidang_id', $user->bidang_id)->where('user_id', $user->id)
            );
        } elseif ($user->hasRole('kabid') && $user->bidang_id) {
            $query->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $user->bidang_id));
        } elseif ($request->filled('bidang_id')) {
            $query->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $request->bidang_id));
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_realisasi', $request->tahun);
        } else {
            $query->whereYear('tanggal_realisasi', now()->year);
        }

        $realisasis = $query->orderByDesc('tanggal_realisasi')->paginate(10);

        $kegiatans = Kegiatan::aktif()
            ->when($user->hasRole('staf'), fn($q) => $q->where('bidang_id', $user->bidang_id)->where('user_id', $user->id))
            ->when($user->hasRole('kabid'), fn($q) => $q->where('bidang_id', $user->bidang_id))
            ->orderBy('nama')->get();

        $subKegiatans = SubKegiatan::whereIn('kegiatan_id', $kegiatans->pluck('id'))->orderBy('nama')->get();
        $bidangs = Bidang::active()->get();

        return view('realisasi.index', compact('realisasis', 'kegiatans', 'subKegiatans', 'bidangs'));
    }

    public function create()
    {
        $user = Auth::user();

        $query = Kegiatan::aktif()->with('bidang');
        if ($user->hasRole('staf')) $query->where('user_id', $user->id)->where('bidang_id', $user->bidang_id);
        elseif ($user->hasRole('kabid')) $query->where('bidang_id', $user->bidang_id);

        $kegiatans = $query->orderBy('nama')->get();
        $subKegiatans = SubKegiatan::whereIn('kegiatan_id', $kegiatans->pluck('id'))->orderBy('nama')->get();
        $rincians = RincianKegiatan::whereIn('sub_kegiatan_id', $subKegiatans->pluck('id'))
            ->select('id','sub_kegiatan_id','uraian','anggaran','kategori')->orderBy('uraian')->get();
        $bidangs = $user->hasRole('admin') ? Bidang::active()->get() : Bidang::where('id', $user->bidang_id)->get();

        return view('realisasi.create', compact('kegiatans', 'subKegiatans', 'rincians', 'bidangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kegiatan_id'        => 'required|exists:kegiatans,id',
            'sub_kegiatan_id'    => 'required|exists:sub_kegiatans,id',
            'tanggal_realisasi'  => 'required|date',
            'lokasi'             => 'nullable|string|max:255',
            'catatan'            => 'nullable|string',
            'rincians'           => 'required|array|min:1',
            'rincians.*.rincian_kegiatan_id' => 'required|exists:rincian_kegiatans,id',
            'rincians.*.realisasi_anggaran'  => 'required|numeric|min:0',
            'rincians.*.realisasi_fisik'     => 'nullable|numeric|min:0|max:100',
        ]);

        $sub = SubKegiatan::findOrFail($request->sub_kegiatan_id);
        if ($sub->kegiatan_id != $request->kegiatan_id)
            return back()->withErrors(['sub_kegiatan_id' => 'Subkegiatan tidak sesuai dengan kegiatan'])->withInput();

        $realisasi = Realisasi::create([
            'kegiatan_id'        => $request->kegiatan_id,
            'sub_kegiatan_id'    => $request->sub_kegiatan_id,
            'user_id'            => Auth::id(),
            'realisasi_fisik'    => 0,
            'realisasi_anggaran' => 0,
            'tanggal_realisasi'  => $request->tanggal_realisasi,
            'lokasi'             => $request->lokasi,
            'catatan'            => $request->catatan,
            'status'             => 'submitted',
        ]);

        $total = 0;
        foreach ($request->rincians as $r) {
            RealisasiRincian::create([
                'realisasi_id'        => $realisasi->id,
                'rincian_kegiatan_id' => $r['rincian_kegiatan_id'],
                'user_id'             => Auth::id(),
                'realisasi_anggaran'  => $r['realisasi_anggaran'],
                'realisasi_fisik'     => $r['realisasi_fisik'] ?? null,
            ]);
            $total += $r['realisasi_anggaran'];
        }

        $avgFisik = RealisasiRincian::where('realisasi_id', $realisasi->id)->avg('realisasi_fisik') ?? 0;

        $realisasi->update([
            'realisasi_anggaran' => $total,
            'realisasi_fisik'    => $avgFisik,
        ]);

        $this->updateSubKegiatanProgress($realisasi->sub_kegiatan_id);

        return redirect()->route('realisasi.index')->with('success', 'Realisasi berhasil ditambahkan!');
    }

    public function edit(Realisasi $realisasi)
    {
        $user = Auth::user();

        $realisasi->load([
            'kegiatan.bidang',
            'subKegiatan',
            'realisasiRincians.rincianKegiatan',
            'dokumens',
        ]);

        $query = Kegiatan::aktif()->with('bidang');
        if ($user->hasRole('staf')) $query->where('user_id', $user->id)->where('bidang_id', $user->bidang_id);
        elseif ($user->hasRole('kabid')) $query->where('bidang_id', $user->bidang_id);

        $kegiatans = $query->orderBy('nama')->get();
        $subKegiatans = SubKegiatan::whereIn('kegiatan_id', $kegiatans->pluck('id'))->orderBy('nama')->get();
        $rincians = RincianKegiatan::whereIn('sub_kegiatan_id', $subKegiatans->pluck('id'))
            ->select('id','sub_kegiatan_id','uraian','anggaran','kategori')->orderBy('uraian')->get();

        return view('realisasi.edit', compact('realisasi','kegiatans','subKegiatans','rincians'));
    }

    public function update(Request $request, Realisasi $realisasi)
    {
        $request->validate([
            'kegiatan_id'        => 'required|exists:kegiatans,id',
            'sub_kegiatan_id'    => 'required|exists:sub_kegiatans,id',
            'tanggal_realisasi'  => 'required|date',
            'lokasi'             => 'nullable|string|max:255',
            'catatan'            => 'nullable|string',
            'rincians'           => 'required|array|min:1',
            'rincians.*.rincian_kegiatan_id' => 'required|exists:rincian_kegiatans,id',
            'rincians.*.realisasi_anggaran'  => 'required|numeric|min:0',
            'rincians.*.realisasi_fisik'     => 'nullable|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request, $realisasi) {
            $realisasi->realisasiRincians()->delete();
            $total = 0;

            foreach ($request->rincians as $r) {
                RealisasiRincian::create([
                    'realisasi_id'        => $realisasi->id,
                    'rincian_kegiatan_id' => $r['rincian_kegiatan_id'],
                    'user_id'             => Auth::id(),
                    'realisasi_anggaran'  => $r['realisasi_anggaran'],
                    'realisasi_fisik'     => $r['realisasi_fisik'] ?? null,
                    'tanggal_realisasi'   => $r['tanggal_realisasi'] ?? $request->tanggal_realisasi, 
                    'lokasi'              => $r['lokasi'] ?? $request->lokasi,
                    'catatan'             => $r['catatan'] ?? null,
                ]);

                $total += $r['realisasi_anggaran'];
            }

            $avgFisik = RealisasiRincian::where('realisasi_id', $realisasi->id)->avg('realisasi_fisik') ?? 0;

            $realisasi->update([
                'kegiatan_id'        => $request->kegiatan_id,
                'sub_kegiatan_id'    => $request->sub_kegiatan_id,
                'tanggal_realisasi'  => $request->tanggal_realisasi,
                'lokasi'             => $request->lokasi,
                'catatan'            => $request->catatan,
                'realisasi_anggaran' => $total,
                'realisasi_fisik'    => $avgFisik,
            ]);

            $this->updateSubKegiatanProgress($realisasi->sub_kegiatan_id);
        });

        return redirect()->route('realisasi.index')->with('success', 'Realisasi berhasil diperbarui!');
    }

    public function show(Realisasi $realisasi)
    {
        $realisasi->load([
            'kegiatan.bidang',
            'subKegiatan',
            'user',
            'dokumens',
            'realisasiRincians.rincianKegiatan'
        ]);

        return view('realisasi.show', compact('realisasi'));
    }

    public function destroy(Realisasi $realisasi)
    {
        DB::transaction(function () use ($realisasi) {
            $realisasi->load(['dokumens', 'realisasiRincians']);
            foreach ($realisasi->dokumens as $doc) {
                if (Storage::disk('public')->exists($doc->path)) Storage::disk('public')->delete($doc->path);
                $doc->delete();
            }
            $realisasi->realisasiRincians()->delete();
            $realisasi->delete();
        });

        $this->updateSubKegiatanProgress($realisasi->sub_kegiatan_id);
        return redirect()->route('realisasi.index')->with('success', 'Realisasi dan dokumen berhasil dihapus.');
    }

    private function updateSubKegiatanProgress(int $subKegiatanId): void
    {
        $totalAnggaran = RealisasiRincian::whereHas('rincianKegiatan', fn($q) => 
            $q->where('sub_kegiatan_id', $subKegiatanId)
        )->sum('realisasi_anggaran');

        $avgFisik = RealisasiRincian::whereHas('rincianKegiatan', fn($q) => 
            $q->where('sub_kegiatan_id', $subKegiatanId)
        )->avg('realisasi_fisik');

        SubKegiatan::where('id', $subKegiatanId)->update([
            'realisasi_anggaran' => $totalAnggaran,
            'realisasi_fisik'    => $avgFisik ?? 0,
        ]);
    }

    private function getJenisDokumen($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) return 'foto';
        if ($mimeType === 'application/pdf') return 'laporan';
        if (str_starts_with($mimeType, 'application/')) return 'kwitansi';
        return 'lainnya';
    }
}
