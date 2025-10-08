<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Kegiatan;
use App\Models\Realisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class RealisasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Realisasi::with(['kegiatan.bidang', 'user', 'dokumens']);

        // Filter berdasarkan bidang jika user adalah staf
        if (Auth::user()->role === 'staf') {
            $query->whereHas('kegiatan', function ($q) {
                $q->where('bidang_id', Auth::user()->bidang_id);
            });
        }

        if ($request->filled('kegiatan_id')) {
            $query->where('kegiatan_id', $request->kegiatan_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $realisasis = $query->paginate(10);
        $kegiatans  = Kegiatan::aktif()->get();

        return view('realisasi.index', compact('realisasis', 'kegiatans'));
    }

    public function create()
    {
        $query = Kegiatan::aktif()->with('bidang');

        // Filter berdasarkan bidang jika user adalah staf
        if (Auth::user()->role === 'staf') {
            $query->where('bidang_id', Auth::user()->bidang_id);
        }

        $kegiatans = $query->get();

        return view('realisasi.create', compact('kegiatans'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'kegiatan_id'        => 'required|exists:kegiatans,id',
            'realisasi_fisik'    => 'required|numeric|min:0|max:100',
            'realisasi_anggaran' => 'required|numeric|min:0',
            'tanggal_realisasi'  => 'required|date',
            'lokasi'             => 'nullable|string|max:255',
            'catatan'            => 'nullable|string',
            'dokumen.*'          => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $realisasi = Realisasi::create([
            'kegiatan_id'        => $request->kegiatan_id,
            'user_id'            => Auth::id(),
            'realisasi_fisik'    => $request->realisasi_fisik,
            'realisasi_anggaran' => $request->realisasi_anggaran,
            'tanggal_realisasi'  => $request->tanggal_realisasi,
            'lokasi'             => $request->lokasi,
            'catatan'            => $request->catatan,
            // default sesuai create: langsung "submitted"
            'status'             => 'submitted',
        ]);

        // Upload dokumen
        if ($request->hasFile('dokumen')) {
            foreach ($request->file('dokumen') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path     = $file->storeAs('dokumen', $fileName, 'public');

                Dokumen::create([
                    'realisasi_id' => $realisasi->id,
                    'nama_file'    => $fileName,
                    'nama_asli'    => $file->getClientOriginalName(),
                    'path'         => $path,
                    'mime_type'    => $file->getMimeType(),
                    'size'         => $file->getSize(),
                    'jenis'        => $this->getJenisDokumen($file->getMimeType()),
                ]);
            }
        }

        return redirect()->route('realisasi.index')->with('success', 'Realisasi berhasil ditambahkan!');
    }

    private function getJenisDokumen($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'foto';
        } elseif ($mimeType === 'application/pdf') {
            return 'laporan';
        } elseif (str_starts_with($mimeType, 'application/')) {
            return 'kwitansi';
        }
        return 'lainnya';
    }

    /** =========================
     *  SHOW + DOWNLOAD + WORKFLOW
     *  ========================= */

    public function show(Realisasi $realisasi)
    {
        // Eager load data lengkap untuk halaman detail
        $realisasi->load([
            'kegiatan.bidang',
            'user',
            'dokumens',
            // 'logs.user', // aktifkan bila ada riwayat status
        ]);

        return view('realisasi.show', compact('realisasi'));
    }

    public function preview(Realisasi $realisasi, Dokumen $dokumen)

    {
        abort_if($dokumen->realisasi_id !== $realisasi->id, 404);

        $disk = Storage::disk('public'); // kalau privat pakai 'local'
        abort_unless($disk->exists($dokumen->path), 404);

        $absPath = $disk->path($dokumen->path);
        $mime    = $dokumen->mime_type ?: mime_content_type($absPath);
        $name    = $dokumen->nama_asli ?: $dokumen->nama_file;

        return response()->file($absPath, [
            'Content-Type'            => $mime ?: 'application/pdf',
            'Content-Disposition'     => 'inline; filename="'.addslashes($name).'"',
            'X-Content-Type-Options'  => 'nosniff',
        ]);
    }

    // (Download kamu sudah punya; biarkan sebagai attachment)
    public function download(Realisasi $realisasi, Dokumen $dokumen)
    
    {
        abort_if($dokumen->realisasi_id !== $realisasi->id, 404);
        $disk = Storage::disk('public');
        abort_unless($disk->exists($dokumen->path), 404);
        $downloadName = $dokumen->nama_asli ?: $dokumen->nama_file;
        return $disk->download($dokumen->path, $downloadName);
    }

    /** Unduh semua dokumen (ZIP) */
    public function downloadAll(Realisasi $realisasi)
    {
        $realisasi->load('dokumens');

        if ($realisasi->dokumens->isEmpty()) {
            return back()->with('warning', 'Tidak ada dokumen untuk diunduh.');
        }

        $zipName = 'realisasi_'.$realisasi->id.'_dokumen.zip';
        $zipPath = storage_path('app/public/tmp/'.$zipName);

        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0775, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat arsip ZIP.');
        }

        foreach ($realisasi->dokumens as $doc) {
            $abs = Storage::disk('public')->path($doc->path);
            if (file_exists($abs)) {
                $entryName = $doc->nama_asli ?: $doc->nama_file;
                $zip->addFile($abs, $entryName);
            }
        }
        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /** Submit (mis. dari draft ke submitted) */
    public function submit(Realisasi $realisasi)
    {
        // Gate/Policy opsional
        if (Gate::denies('submit-realisasi', $realisasi)) {
            abort(403);
        }

        if ($realisasi->status === 'draft') {
            $realisasi->update(['status' => 'submitted']);
            // Tulis log jika ada
        }

        return back()->with('success', 'Realisasi telah dikirim (submitted).');
    }

    /** Approve (khusus role berwenang) */
    public function approve(Realisasi $realisasi)
    {
        if (Gate::denies('approve-realisasi', $realisasi)) {
            abort(403);
        }

        if ($realisasi->status === 'submitted') {
            $realisasi->update(['status' => 'approved']);
            // Tulis log jika ada
        }

        return back()->with('success', 'Realisasi disetujui.');
    }

    /** Reject (khusus role berwenang) */
    public function reject(Realisasi $realisasi, Request $request)
    {
        if (Gate::denies('approve-realisasi', $realisasi)) {
            abort(403);
        }

        if ($realisasi->status === 'submitted') {
            $realisasi->update(['status' => 'rejected']);
            // Simpan catatan penolakan dari $request->catatan bila perlu
        }

        return back()->with('success', 'Realisasi ditolak.');
    }
}
