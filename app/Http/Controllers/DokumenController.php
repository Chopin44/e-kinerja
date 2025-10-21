<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Realisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DokumenController extends Controller
{
    public function store(Request $request, Realisasi $realisasi)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $file = $request->file('file');
        $path = $file->store('dokumen_realisasi', 'public');

        Dokumen::create([
            'realisasi_id' => $realisasi->id,
            'nama_file'    => $file->hashName(),
            'nama_asli'    => $file->getClientOriginalName(),
            'path'         => $path,
            'mime_type'    => $file->getMimeType(),
            'size'         => $file->getSize(),
            'jenis'        => $this->getJenisDokumen($file->getMimeType()),
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah!');
    }

    public function destroy(Dokumen $dokumen)
    {
        // Hapus file fisik di storage
        if ($dokumen->path && Storage::disk('public')->exists($dokumen->path)) {
            Storage::disk('public')->delete($dokumen->path);
        }

        $dokumen->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    private function getJenisDokumen($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) return 'foto';
        if ($mimeType === 'application/pdf') return 'laporan';
        if (str_starts_with($mimeType, 'application/')) return 'kwitansi';
        return 'lainnya';
    }
}
