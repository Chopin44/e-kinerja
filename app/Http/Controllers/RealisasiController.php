<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Kegiatan;
use App\Models\Realisasi;
use App\Models\SubKegiatan;
use App\Models\Bidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RealisasiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Realisasi::with([
            'kegiatan.bidang',
            'subKegiatan',        // ← eager load subkegiatan
            'user',
            'dokumens'
        ]);

        // 🔒 Pembatasan data berdasarkan role
        if ($user->role === 'staf') {
            // Staf: realisasi dari kegiatan miliknya sendiri di bidangnya
            $query->whereHas('kegiatan', function ($q) use ($user) {
                $q->where('bidang_id', $user->bidang_id)
                  ->where('user_id', $user->id);
            });
        } elseif ($user->role === 'pimpinan' && $user->bidang_id) {
            // Pimpinan: realisasi dari bidangnya
            $query->whereHas('kegiatan', function ($q) use ($user) {
                $q->where('bidang_id', $user->bidang_id);
            });
        } elseif ($request->filled('bidang_id')) {
            // Admin: filter manual by bidang
            $query->whereHas('kegiatan', function ($q) use ($request) {
                $q->where('bidang_id', $request->bidang_id);
            });
        }

        // 🎯 Filter kegiatan
        if ($request->filled('kegiatan_id')) {
            $query->where('kegiatan_id', $request->kegiatan_id);
        }

        // 🎯 (opsional) Filter subkegiatan
        if ($request->filled('sub_kegiatan_id')) {
            $query->where('sub_kegiatan_id', $request->sub_kegiatan_id);
        }

        // 🎯 Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 🎯 Filter tahun realisasi
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_realisasi', $request->tahun);
        } else {
            $query->whereYear('tanggal_realisasi', Carbon::now()->year);
        }

        $realisasis = $query->orderByDesc('tanggal_realisasi')->paginate(10);

        // 🔁 Dropdown kegiatan menyesuaikan bidang & role
        if ($user->role === 'staf') {
            $kegiatans = Kegiatan::where('bidang_id', $user->bidang_id)
                ->where('user_id', $user->id)
                ->aktif()
                ->get();
        } elseif ($user->role === 'pimpinan' && $user->bidang_id) {
            $kegiatans = Kegiatan::where('bidang_id', $user->bidang_id)->aktif()->get();
        } else {
            $kegiatans = Kegiatan::aktif()->get();
        }

        // List subkegiatan untuk dropdown filter (sesuai jangkauan kegiatan di atas)
        $subKegiatans = SubKegiatan::whereIn('kegiatan_id', $kegiatans->pluck('id'))
            ->orderBy('nama')
            ->get();

        // Dropdown bidang (khusus admin)
        $bidangs = Bidang::active()->get();

        return view('realisasi.index', compact('realisasis', 'kegiatans', 'subKegiatans', 'bidangs'));
    }

    public function create()
    {
        $user = Auth::user();

        // Base query hanya kegiatan aktif
        $query = Kegiatan::aktif()->with('bidang');

        // 🔒 Filter kegiatan sesuai role
        if ($user->hasRole('staf')) {
            $query->where('user_id', $user->id)
                  ->where('bidang_id', $user->bidang_id);
        } elseif ($user->hasRole('pimpinan')) {
            $query->where('bidang_id', $user->bidang_id);
        }

        $kegiatans = $query->orderBy('nama')->get();

        // Ambil subkegiatan dari kegiatan yang boleh diakses
        $subKegiatans = SubKegiatan::whereIn('kegiatan_id', $kegiatans->pluck('id'))
            ->orderBy('nama')
            ->get();

        // Admin: semua bidang, lainnya: bidang sendiri
        if ($user->hasRole('admin')) {
            $bidangs = Bidang::active()->get();
        } else {
            $bidangs = Bidang::where('id', $user->bidang_id)->get();
        }

        return view('realisasi.create', compact('kegiatans', 'subKegiatans', 'bidangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kegiatan_id'        => 'required|exists:kegiatans,id',
            'sub_kegiatan_id'    => 'nullable|exists:sub_kegiatans,id', // opsional
            'realisasi_fisik'    => 'required|numeric|min:0|max:100',
            'realisasi_anggaran' => 'required|numeric|min:0',
            'tanggal_realisasi'  => 'required|date',
            'lokasi'             => 'nullable|string|max:255',
            'catatan'            => 'nullable|string',
            'dokumen.*'          => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        // Validasi konsistensi: jika sub_kegiatan_id diisi, harus milik kegiatan_id yang sama
        if ($request->filled('sub_kegiatan_id')) {
            $sub = SubKegiatan::find($request->sub_kegiatan_id);
            if (!$sub || (int)$sub->kegiatan_id !== (int)$request->kegiatan_id) {
                return back()
                    ->withInput()
                    ->withErrors(['sub_kegiatan_id' => 'Subkegiatan tidak sesuai dengan Kegiatan yang dipilih.']);
            }
        }

        $realisasi = Realisasi::create([
            'kegiatan_id'        => $request->kegiatan_id,
            'sub_kegiatan_id'    => $request->sub_kegiatan_id, // bisa null
            'user_id'            => Auth::id(),
            'realisasi_fisik'    => $request->realisasi_fisik,
            'realisasi_anggaran' => $request->realisasi_anggaran,
            'tanggal_realisasi'  => $request->tanggal_realisasi,
            'lokasi'             => $request->lokasi,
            'catatan'            => $request->catatan,
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

    public function show(Realisasi $realisasi)
    {
        $realisasi->load(['kegiatan.bidang', 'subKegiatan', 'user', 'dokumens']);
        return view('realisasi.show', compact('realisasi'));
    }

    public function preview(Realisasi $realisasi, Dokumen $dokumen)
    {
        abort_if($dokumen->realisasi_id !== $realisasi->id, 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($dokumen->path), 404);

        $absPath = $disk->path($dokumen->path);
        $mime    = $dokumen->mime_type ?: mime_content_type($absPath);
        $name    = $dokumen->nama_asli ?: $dokumen->nama_file;

        return response()->file($absPath, [
            'Content-Type'            => $mime ?: 'application/pdf',
            'Content-Disposition'     => 'inline; filename="' . addslashes($name) . '"',
            'X-Content-Type-Options'  => 'nosniff',
        ]);
    }

    public function download(Realisasi $realisasi, Dokumen $dokumen)
    {
        abort_if($dokumen->realisasi_id !== $realisasi->id, 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($dokumen->path), 404);

        $downloadName = $dokumen->nama_asli ?: $dokumen->nama_file;
        return $disk->download($dokumen->path, $downloadName);
    }

    public function downloadAll(Realisasi $realisasi)
    {
        $realisasi->load('dokumens');

        if ($realisasi->dokumens->isEmpty()) {
            return back()->with('warning', 'Tidak ada dokumen untuk diunduh.');
        }

        $zipName = 'realisasi_' . $realisasi->id . '_dokumen.zip';
        $zipPath = storage_path('app/public/tmp/' . $zipName);

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

    /** Workflow (Submit, Approve, Reject) **/
    public function submit(Realisasi $realisasi)
    {
        if (Gate::denies('submit-realisasi', $realisasi)) abort(403);
        if ($realisasi->status === 'draft') {
            $realisasi->update(['status' => 'submitted']);
        }
        return back()->with('success', 'Realisasi telah dikirim (submitted).');
    }

    public function approve(Realisasi $realisasi)
    {
        if (Gate::denies('approve-realisasi', $realisasi)) abort(403);
        if ($realisasi->status === 'submitted') {
            $realisasi->update(['status' => 'approved']);
        }
        return back()->with('success', 'Realisasi disetujui.');
    }

    public function reject(Realisasi $realisasi, Request $request)
    {
        if (Gate::denies('approve-realisasi', $realisasi)) abort(403);
        if ($realisasi->status === 'submitted') {
            $realisasi->update(['status' => 'rejected']);
        }
        return back()->with('success', 'Realisasi ditolak.');
    }
}
