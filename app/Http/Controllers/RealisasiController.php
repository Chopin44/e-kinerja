<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Dokumen;
use App\Models\Kegiatan;
use App\Models\Realisasi;
use App\Models\RealisasiRincian;
use App\Models\RincianKegiatan;
use App\Models\SubKegiatan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RealisasiController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Realisasi::with([
            'kegiatan.bidang',
            'subKegiatan',
            'user',
            // ⬇️ konsisten dengan Blade
            'realisasiRincians.rincianKegiatan:id,uraian,kategori'
        ])
        ->withCount(['realisasiRincians as rincian_count'])
        ->withSum('realisasiRincians as rincian_total_anggaran', 'realisasi_anggaran');


        // 🔒 filter role & parameter (biarkan seperti punyamu)
        if ($user->hasRole('staf')) {
            $query->whereHas('kegiatan', function ($q) use ($user) {
                $q->where('bidang_id', $user->bidang_id)
                ->where('user_id', $user->id);
            });
        } elseif ($user->hasRole('kabid') && $user->bidang_id) {
            $query->whereHas('kegiatan', function ($q) use ($user) {
                $q->where('bidang_id', $user->bidang_id);
            });
        } elseif ($request->filled('bidang_id')) {
            $query->whereHas('kegiatan', function ($q) use ($request) {
                $q->where('bidang_id', $request->bidang_id);
            });
        }

        if ($request->filled('kegiatan_id')) {
            $query->where('kegiatan_id', $request->kegiatan_id);
        }
        if ($request->filled('sub_kegiatan_id')) {
            $query->where('sub_kegiatan_id', $request->sub_kegiatan_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_realisasi', $request->tahun);
        } else {
            $query->whereYear('tanggal_realisasi', \Carbon\Carbon::now()->year);
        }

        $realisasis = $query->orderByDesc('tanggal_realisasi')->paginate(10);

        // dropdown (tetap seperti punyamu)
        if ($user->hasRole('staf')) {
            $kegiatans = \App\Models\Kegiatan::where('bidang_id', $user->bidang_id)
                ->where('user_id', $user->id)->aktif()->orderBy('nama')->get();
        } elseif ($user->hasRole('kabid') && $user->bidang_id) {
            $kegiatans = \App\Models\Kegiatan::where('bidang_id', $user->bidang_id)
                ->aktif()->orderBy('nama')->get();
        } else {
            $kegiatans = \App\Models\Kegiatan::aktif()->orderBy('nama')->get();
        }

        $subKegiatans = \App\Models\SubKegiatan::whereIn('kegiatan_id', $kegiatans->pluck('id'))
            ->orderBy('nama')->get();

        $bidangs = \App\Models\Bidang::active()->get();

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
        } elseif ($user->hasRole('kabid')) {
            $query->where('bidang_id', $user->bidang_id);
        }

        $kegiatans = $query->orderBy('nama')->get();

        // Subkegiatan dari kegiatan yang boleh diakses
        $subKegiatans = SubKegiatan::whereIn('kegiatan_id', $kegiatans->pluck('id'))
            ->orderBy('nama')
            ->get();

        // Rincian dari subkegiatan di atas (akan difilter lagi di sisi UI sesuai sub terpilih)
        $rincians = RincianKegiatan::whereIn('sub_kegiatan_id', $subKegiatans->pluck('id'))
            ->select('id','sub_kegiatan_id','uraian','anggaran','kategori')
            ->orderBy('uraian')
            ->get();

        // Admin: semua bidang, lainnya: bidang sendiri (opsional untuk info)
        if ($user->hasRole('admin')) {
            $bidangs = Bidang::active()->get();
        } else {
            $bidangs = Bidang::where('id', $user->bidang_id)->get();
        }

        return view('realisasi.create', compact('kegiatans', 'subKegiatans', 'rincians', 'bidangs'));
    }

    public function store(Request $request)
    {
        // Header wajib SubKegiatan; total anggaran header akan dihitung dari detail
        $request->validate([
            'kegiatan_id'        => ['required','exists:kegiatans,id'],
            'sub_kegiatan_id'    => ['required','exists:sub_kegiatans,id'],
            'realisasi_fisik'    => ['nullable','numeric','min:0','max:100'],
            'tanggal_realisasi'  => ['required','date'],
            'lokasi'             => ['nullable','string','max:255'],
            'catatan'            => ['nullable','string'],
            // Detail (repeater)
            'rincians'                                      => ['required','array','min:1'],
            'rincians.*.rincian_kegiatan_id'                => ['required','exists:rincian_kegiatans,id'],
            'rincians.*.realisasi_anggaran'                 => ['required','numeric','min:0'],
            'rincians.*.realisasi_fisik'                    => ['nullable','numeric','min:0','max:100'],
            'rincians.*.realisasi_volume'                   => ['nullable','numeric','min:0'],
            'rincians.*.tanggal_realisasi'                  => ['nullable','date'],
            'rincians.*.lokasi'                             => ['nullable','string','max:255'],
            'rincians.*.catatan'                            => ['nullable','string'],
            // Dokumen
            'dokumen.*'          => ['nullable','file','max:10240','mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        // validasi sub ↔ kegiatan
        $sub = SubKegiatan::findOrFail($request->sub_kegiatan_id);
        if ((int)$sub->kegiatan_id !== (int)$request->kegiatan_id) {
            return back()
                ->withInput()
                ->withErrors(['sub_kegiatan_id' => 'Subkegiatan tidak sesuai dengan Kegiatan yang dipilih.']);
        }

        // validasi: setiap rincian harus milik sub yang sama
        $validRincianIds = RincianKegiatan::where('sub_kegiatan_id', $sub->id)->pluck('id')->all();
        foreach ($request->rincians as $i => $row) {
            if (!in_array($row['rincian_kegiatan_id'], $validRincianIds)) {
                return back()
                    ->withInput()
                    ->withErrors(["rincians.$i.rincian_kegiatan_id" => 'Rincian tidak termasuk subkegiatan terpilih.']);
            }
        }

        // Buat header realisasi (total anggaran akan diisi setelah simpan detail)
        $realisasi = Realisasi::create([
            'kegiatan_id'        => $request->kegiatan_id,
            'sub_kegiatan_id'    => $request->sub_kegiatan_id,
            'user_id'            => Auth::id(),
            'realisasi_fisik'    => $request->realisasi_fisik ?? 0,
            'realisasi_anggaran' => 0,
            'tanggal_realisasi'  => $request->tanggal_realisasi,
            'lokasi'             => $request->lokasi,
            'catatan'            => $request->catatan,
            'status'             => 'submitted',
        ]);

        // Simpan detail dan hitung total
        $totalDetail = 0;
        foreach ($request->rincians as $row) {
            $detail = RealisasiRincian::create([
                'realisasi_id'        => $realisasi->id,
                'rincian_kegiatan_id' => $row['rincian_kegiatan_id'],
                'user_id'             => Auth::id(),
                'realisasi_anggaran'  => $row['realisasi_anggaran'],
                'realisasi_fisik'     => $row['realisasi_fisik'] ?? null,
                'realisasi_volume'    => $row['realisasi_volume'] ?? null,
                'tanggal_realisasi'   => $row['tanggal_realisasi'] ?? $request->tanggal_realisasi,
                'lokasi'              => $row['lokasi'] ?? $request->lokasi,
                'catatan'             => $row['catatan'] ?? null,
            ]);

            $totalDetail += (float) $detail->realisasi_anggaran;
        }

        // Update total header
        $realisasi->update([
            'realisasi_anggaran' => $totalDetail,
        ]);

        // Upload dokumen (opsional)
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

        return redirect()->route('realisasi.index')->with('success', 'Realisasi beserta rincian berhasil ditambahkan!');
    }

    // taruh di dalam class RealisasiController
    public function destroy(Realisasi $realisasi)
    {
        $user = Auth::user();

        // Boleh hapus jika: admin/kabid ATAU (pemilik & masih draft)
        $isManager = $user->hasAnyRole(['admin','kabid']);
        $isOwnerDraft = ($realisasi->status === 'draft' && (int)$realisasi->user_id === (int)$user->id);

        if (!($isManager || $isOwnerDraft)) {
            abort(403, 'Anda tidak berwenang menghapus realisasi ini.');
        }

        DB::transaction(function () use ($realisasi) {
            // Load relasi yang akan dihapus
            $realisasi->load(['dokumens', 'realisasiRincians']);

            // Hapus file dokumen dari storage + record-nya
            foreach ($realisasi->dokumens as $doc) {
                if ($doc->path && Storage::disk('public')->exists($doc->path)) {
                    Storage::disk('public')->delete($doc->path);
                }
                $doc->delete(); // jika FK onDelete cascade sudah ada, baris ini opsional
            }

            // Hapus realisasi rincian (kalau FK cascade belum di-set)
            foreach ($realisasi->realisasiRincians as $rr) {
                $rr->delete();
            }

            // Hapus header realisasi
            $realisasi->delete();
        });

        return redirect()
            ->route('realisasi.index')
            ->with('success', 'Realisasi beserta rincian dan dokumen berhasil dihapus.');
    }

    // EDIT: tampilkan form edit beserta dropdown & master data yg sama seperti create
    public function edit(Realisasi $realisasi)
    {
        $user = Auth::user();

        $realisasi->load([
            'kegiatan.bidang',
            'subKegiatan',
            'realisasiRincians.rincianKegiatan',
            'dokumens',
        ]);

        // NOTE: di halaman edit, aman kalau kamu ingin melepas scope aktif()
        // supaya item yang diedit pasti terlihat. Kalau tetap mau aktif(), push di bawah.
        $query = Kegiatan::aktif()->with('bidang');

        if ($user->hasRole('staf')) {
            $query->where('user_id', $user->id)->where('bidang_id', $user->bidang_id);
        } elseif ($user->hasRole('kabid')) {
            $query->where('bidang_id', $user->bidang_id);
        }

        $kegiatans = $query->orderBy('nama')->get();

        $subKegiatans = SubKegiatan::whereIn('kegiatan_id', $kegiatans->pluck('id'))
            ->orderBy('nama')->get();

        // ⛳ PASTIKAN yang sedang diedit masuk ke dropdown
        if ($realisasi->kegiatan && !$kegiatans->contains('id', $realisasi->kegiatan_id)) {
            $kegiatans->push($realisasi->kegiatan);
            $kegiatans = $kegiatans->unique('id')->sortBy('nama')->values();
        }
        if ($realisasi->subKegiatan && !$subKegiatans->contains('id', $realisasi->sub_kegiatan_id)) {
            $subKegiatans->push($realisasi->subKegiatan);
            $subKegiatans = $subKegiatans->unique('id')->sortBy('nama')->values();
        }

        $rincians = RincianKegiatan::whereIn('sub_kegiatan_id', $subKegiatans->pluck('id'))
            ->select('id','sub_kegiatan_id','uraian','anggaran','kategori')
            ->orderBy('uraian')->get();

        // kirim view
        return view('realisasi.edit', compact('realisasi','kegiatans','subKegiatans','rincians'));
    }



    // UPDATE: validasi + cek konsistensi + replace-all detail + hitung total
    public function update(Request $request, Realisasi $realisasi)
    {
        $request->validate([
            'kegiatan_id'        => ['required','exists:kegiatans,id'],
            'sub_kegiatan_id'    => ['required','exists:sub_kegiatans,id'],
            'realisasi_fisik'    => ['nullable','numeric','min:0','max:100'],
            'tanggal_realisasi'  => ['required','date'],
            'lokasi'             => ['nullable','string','max:255'],
            'catatan'            => ['nullable','string'],

            // Detail (repeater)
            'rincians'                               => ['required','array','min:1'],
            'rincians.*.rincian_kegiatan_id'         => ['required','exists:rincian_kegiatans,id'],
            'rincians.*.realisasi_anggaran'          => ['required','numeric','min:0'],
            'rincians.*.realisasi_fisik'             => ['nullable','numeric','min:0','max:100'],
            'rincians.*.realisasi_volume'            => ['nullable','numeric','min:0'],
            'rincians.*.tanggal_realisasi'           => ['nullable','date'],
            'rincians.*.lokasi'                      => ['nullable','string','max:255'],
            'rincians.*.catatan'                     => ['nullable','string'],

            // Dokumen tambahan saat edit (opsional, di-append)
            'dokumen.*'          => ['nullable','file','max:10240','mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        // validasi sub ↔ kegiatan
        $sub = SubKegiatan::findOrFail($request->sub_kegiatan_id);
        if ((int) $sub->kegiatan_id !== (int) $request->kegiatan_id) {
            return back()
                ->withInput()
                ->withErrors(['sub_kegiatan_id' => 'Subkegiatan tidak sesuai dengan Kegiatan yang dipilih.']);
        }

        // validasi: setiap rincian harus milik sub yang sama
        $validRincianIds = RincianKegiatan::where('sub_kegiatan_id', $sub->id)->pluck('id')->all();
        foreach ($request->rincians as $i => $row) {
            if (!in_array($row['rincian_kegiatan_id'], $validRincianIds)) {
                return back()
                    ->withInput()
                    ->withErrors(["rincians.$i.rincian_kegiatan_id" => 'Rincian tidak termasuk subkegiatan terpilih.']);
            }
        }

        DB::transaction(function () use ($request, $realisasi) {
            // Update header (biarkan status apa adanya)
            $realisasi->update([
                'kegiatan_id'        => $request->kegiatan_id,
                'sub_kegiatan_id'    => $request->sub_kegiatan_id,
                'realisasi_fisik'    => $request->realisasi_fisik ?? 0,
                'tanggal_realisasi'  => $request->tanggal_realisasi,
                'lokasi'             => $request->lokasi,
                'catatan'            => $request->catatan,
            ]);

            // Hapus semua detail lama lalu buat ulang
            $realisasi->realisasiRincians()->delete();

            $totalDetail = 0;
            foreach ($request->rincians as $row) {
                $detail = RealisasiRincian::create([
                    'realisasi_id'        => $realisasi->id,
                    'rincian_kegiatan_id' => $row['rincian_kegiatan_id'],
                    'user_id'             => Auth::id(),
                    'realisasi_anggaran'  => $row['realisasi_anggaran'],
                    'realisasi_fisik'     => $row['realisasi_fisik'] ?? null,
                    'realisasi_volume'    => $row['realisasi_volume'] ?? null,
                    'tanggal_realisasi'   => $row['tanggal_realisasi'] ?? $request->tanggal_realisasi,
                    'lokasi'              => $row['lokasi'] ?? $request->lokasi,
                    'catatan'             => $row['catatan'] ?? null,
                ]);

                $totalDetail += (float) $detail->realisasi_anggaran;
            }

            // Update total header mengikuti jumlah detail
            $realisasi->update([
                'realisasi_anggaran' => $totalDetail,
            ]);

            // Tambahkan dokumen baru (append)
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
        });

        return redirect()->route('realisasi.index')->with('success', 'Realisasi berhasil diperbarui!');
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
        $realisasi->load([
            'kegiatan.bidang',
            'subKegiatan',
            'user',
            'dokumens',
            'rincians.rincianKegiatan' // tampilkan detail juga
        ]);
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

    /** Workflow (opsional) **/
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
