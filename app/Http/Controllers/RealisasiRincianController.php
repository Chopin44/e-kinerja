<?php

namespace App\Http\Controllers;

use App\Models\RealisasiRincian;
use App\Models\RincianKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RealisasiRincianController extends Controller
{
    // CREATE
    public function create(RincianKegiatan $rincian)
    {
        $this->authorizeByRincian($rincian);
        $rincian->load('subKegiatan.kegiatan.bidang');
        return view('realisasi_rincian.create', compact('rincian'));
    }

    // STORE
    public function store(Request $request, RincianKegiatan $rincian)
    {
        $this->authorizeByRincian($rincian);

        $data = $request->validate([
            'realisasi_anggaran' => ['required','numeric','min:0'],
            'realisasi_fisik'    => ['nullable','numeric','min:0','max:100'],
            'tanggal_realisasi'  => ['required','date'],
            'lokasi'             => ['nullable','string','max:255'],
            'catatan'            => ['nullable','string'],
        ]);

        $data['rincian_kegiatan_id'] = $rincian->id;
        $data['user_id']             = Auth::id();

        // simpan record
        RealisasiRincian::create($data);

        // update total dan fisik subkegiatan otomatis
        $this->updateSubProgress($rincian->sub_kegiatan_id);
        

        return redirect()
            ->route('subkegiatan.rincian.index', $rincian->subKegiatan)
            ->with('success', 'Realisasi rincian berhasil ditambahkan.');
    }

    // EDIT
    public function edit(RealisasiRincian $realisasi_rincian)
    {
        $rincian = $realisasi_rincian->rincianKegiatan()->with('subKegiatan.kegiatan.bidang')->firstOrFail();
        $this->authorizeByRincian($rincian);

        return view('realisasi_rincian.edit', [
            'realisasi' => $realisasi_rincian,
            'rincian'   => $rincian,
        ]);
    }

    // UPDATE
    public function update(Request $request, RealisasiRincian $realisasi_rincian)
    {
        $rincian = $realisasi_rincian->rincianKegiatan()->with('subKegiatan.kegiatan.bidang')->firstOrFail();
        $this->authorizeByRincian($rincian);

        $data = $request->validate([
            'realisasi_anggaran' => ['required','numeric','min:0'],
            'realisasi_fisik'    => ['nullable','numeric','min:0','max:100'],
            'tanggal_realisasi'  => ['required','date'],
            'lokasi'             => ['nullable','string','max:255'],
            'catatan'            => ['nullable','string'],
        ]);

        $realisasi_rincian->update($data);

        $this->updateSubKegiatanProgress($rincian->subKegiatan->id);


        // update agregat subkegiatan
        $this->updateSubProgress($rincian->sub_kegiatan_id);

        return redirect()
            ->route('subkegiatan.rincian.index', $rincian->subKegiatan)
            ->with('success', 'Realisasi rincian berhasil diperbarui.');
    }

    // DESTROY
    public function destroy(RealisasiRincian $realisasi_rincian)
    {
        $rincian = $realisasi_rincian->rincianKegiatan()->with('subKegiatan.kegiatan.bidang')->firstOrFail();
        $this->authorizeByRincian($rincian);

        $realisasi_rincian->delete();
        $this->updateSubKegiatanProgress($rincian->subKegiatan->id);


        $this->updateSubProgress($rincian->sub_kegiatan_id);

        return redirect()
            ->route('subkegiatan.rincian.index', $rincian->subKegiatan)
            ->with('success', 'Realisasi rincian berhasil dihapus.');
    }

    /**
     * Update progres subkegiatan (fisik & anggaran)
     */
    private function updateSubProgress(int $subId): void
    {
        $totalAnggaran = \App\Models\RealisasiRincian::whereHas('rincianKegiatan', fn($q) =>
            $q->where('sub_kegiatan_id', $subId)
        )->sum('realisasi_anggaran');

        $avgFisik = \App\Models\RealisasiRincian::whereHas('rincianKegiatan', fn($q) =>
            $q->where('sub_kegiatan_id', $subId)
        )->avg('realisasi_fisik');

        \App\Models\SubKegiatan::where('id', $subId)->update([
            'realisasi_anggaran' => $totalAnggaran,
            'realisasi_fisik'    => $avgFisik ?? 0,
        ]);
    }

    /**
     * Otorisasi sederhana
     */
    private function authorizeByRincian(RincianKegiatan $r): void
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['admin','kabid','staf'])) return;

        $bidangRincian = (int)($r->subKegiatan->kegiatan->bidang_id ?? -1);
        if ($bidangRincian === (int)($user->bidang_id ?? -2)) return;

        abort(403, 'Anda tidak berwenang mengelola realisasi rincian ini.');
    }

    private function updateSubKegiatanProgress(int $subKegiatanId): void
{
    $totalAnggaran = \App\Models\RealisasiRincian::whereHas('rincianKegiatan', function($q) use ($subKegiatanId) {
        $q->where('sub_kegiatan_id', $subKegiatanId);
    })->sum('realisasi_anggaran');

    $avgFisik = \App\Models\RealisasiRincian::whereHas('rincianKegiatan', function($q) use ($subKegiatanId) {
        $q->where('sub_kegiatan_id', $subKegiatanId);
    })->avg('realisasi_fisik');

    \App\Models\SubKegiatan::where('id', $subKegiatanId)->update([
        'realisasi_anggaran' => $totalAnggaran,
        'realisasi_fisik'    => $avgFisik ?? 0,
    ]);
}

}
