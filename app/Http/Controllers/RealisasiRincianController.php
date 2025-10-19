<?php

namespace App\Http\Controllers;

use App\Models\RealisasiRincian;
use App\Models\RincianKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RealisasiRincianController extends Controller
{
    // CREATE (nested): /rincian/{rincian}/realisasi-rincian/create
    public function create(RincianKegiatan $rincian)
    {
        $this->authorizeByRincian($rincian);
        // info header
        $rincian->load('subKegiatan.kegiatan.bidang');
        return view('realisasi_rincian.create', compact('rincian'));
    }

    // STORE (nested)
    public function store(Request $request, RincianKegiatan $rincian)
    {
        $this->authorizeByRincian($rincian);

        $data = $request->validate([
            'realisasi_anggaran' => ['required','numeric','min:0'],
            'tanggal_realisasi'  => ['required','date'],
            'lokasi'             => ['nullable','string','max:255'],
            'catatan'            => ['nullable','string'],
        ]);

        $data['rincian_kegiatan_id'] = $rincian->id;
        $data['user_id']             = Auth::id();

        RealisasiRincian::create($data);

        // balik ke halaman subkegiatan → daftar rincian yang sudah kamu punya
        return redirect()
            ->route('subkegiatan.rincian.index', $rincian->subKegiatan)
            ->with('success', 'Realisasi rincian berhasil ditambahkan.');
    }

    // EDIT (shallow): /realisasi-rincian/{realisasi_rincian}/edit
    public function edit(RealisasiRincian $realisasi_rincian)
    {
        $rincian = $realisasi_rincian->rincian()->with('subKegiatan.kegiatan.bidang')->firstOrFail();
        $this->authorizeByRincian($rincian);

        return view('realisasi_rincian.edit', [
            'realisasi' => $realisasi_rincian,
            'rincian'   => $rincian,
        ]);
    }

    // UPDATE (shallow)
    public function update(Request $request, RealisasiRincian $realisasi_rincian)
    {
        $rincian = $realisasi_rincian->rincian()->with('subKegiatan.kegiatan.bidang')->firstOrFail();
        $this->authorizeByRincian($rincian);

        $data = $request->validate([
            'realisasi_anggaran' => ['required','numeric','min:0'],
            'tanggal_realisasi'  => ['required','date'],
            'lokasi'             => ['nullable','string','max:255'],
            'catatan'            => ['nullable','string'],
        ]);

        $realisasi_rincian->update($data);

        return redirect()
            ->route('subkegiatan.rincian.index', $rincian->subKegiatan)
            ->with('success', 'Realisasi rincian berhasil diperbarui.');
    }

    // DESTROY (shallow)
    public function destroy(RealisasiRincian $realisasi_rincian)
    {
        $rincian = $realisasi_rincian->rincian()->with('subKegiatan.kegiatan.bidang')->firstOrFail();
        $this->authorizeByRincian($rincian);

        $realisasi_rincian->delete();

        return redirect()
            ->route('subkegiatan.rincian.index', $rincian->subKegiatan)
            ->with('success', 'Realisasi rincian berhasil dihapus.');
    }

    /**
     * Otorisasi sederhana (opsi B): semua user dalam bidang yang sama
     * (atau admin/kabid/pimpinan) boleh kelola realisasi rincian.
     */
    private function authorizeByRincian(RincianKegiatan $r): void
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['admin','kabid','pimpinan'])) {
            return;
        }

        $bidangRincian = (int)($r->subKegiatan->kegiatan->bidang_id ?? -1);
        if ($bidangRincian === (int)($user->bidang_id ?? -2)) {
            return;
        }

        abort(403, 'Anda tidak berwenang mengelola realisasi rincian ini.');
    }
}
