<?php

namespace App\Http\Controllers;

use App\Models\SubKegiatan;
use App\Models\RincianKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RincianKegiatanController extends Controller
{
    /**
     * INDEX (NESTED): /subkegiatan/{subkegiatan}/rincian
     */
    public function index(SubKegiatan $subkegiatan)
    {
        $this->authorizeIndex($subkegiatan);

        $subkegiatan->load('kegiatan.bidang');

        $rincians = $subkegiatan->rincianKegiatans()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('rincian.index', compact('subkegiatan', 'rincians'));
    }

    /**
     * CREATE (NESTED): /subkegiatan/{subkegiatan}/rincian/create
     */
    public function create(SubKegiatan $subkegiatan)
    {
        $this->authorizeManage($subkegiatan);

        $subkegiatan->load('kegiatan.bidang');

        return view('rincian.create', compact('subkegiatan'));
    }

    /**
     * STORE (NESTED): POST /subkegiatan/{subkegiatan}/rincian
     */
    public function store(Request $request, SubKegiatan $subkegiatan)
    {
        $this->authorizeManage($subkegiatan);

        $data = $request->validate([
            'uraian'        => ['required', 'string', 'max:255'],
            'kategori'      => ['nullable', 'in:pengadaan_langsung,swakelola,pokir'],
            'anggaran'      => ['nullable', 'numeric', 'min:0'],
            'target_fisik'  => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $data['sub_kegiatan_id'] = $subkegiatan->id;
        $data['target_fisik'] = $data['target_fisik'] ?? 0;

        RincianKegiatan::create($data);

        return redirect()
            ->route('subkegiatan.rincian.index', $subkegiatan)
            ->with('success', 'Rincian berhasil ditambahkan.');
    }

    /**
     * EDIT (SHALLOW): /rincian/{rincian}/edit
     */
    public function edit(RincianKegiatan $rincian)
    {
        $rincian->load('subKegiatan.kegiatan.bidang');
        $subkegiatan = $rincian->subKegiatan;

        $this->authorizeManage($subkegiatan);

        return view('rincian.edit', compact('rincian', 'subkegiatan'));
    }

    /**
     * UPDATE (SHALLOW): PUT/PATCH /rincian/{rincian}
     */
    public function update(Request $request, RincianKegiatan $rincian)
    {
        $rincian->load('subKegiatan.kegiatan.bidang');
        $subkegiatan = $rincian->subKegiatan;

        $this->authorizeManage($subkegiatan);

        $data = $request->validate([
            'uraian'        => ['required', 'string', 'max:255'],
            'kategori'      => ['nullable', 'in:pengadaan_langsung,swakelola,pokir'],
            'anggaran'      => ['nullable', 'numeric', 'min:0'],
            'target_fisik'  => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $data['target_fisik'] = $data['target_fisik'] ?? 0;

        $rincian->update($data);

        return redirect()
            ->route('subkegiatan.rincian.index', $subkegiatan)
            ->with('success', 'Rincian berhasil diperbarui.');
    }

    /**
     * DESTROY (SHALLOW): DELETE /rincian/{rincian}
     */
    public function destroy(RincianKegiatan $rincian)
    {
        $rincian->load('subKegiatan.kegiatan.bidang');
        $subkegiatan = $rincian->subKegiatan;

        $this->authorizeManage($subkegiatan);

        $rincian->delete();

        return redirect()
            ->route('subkegiatan.rincian.index', $subkegiatan)
            ->with('success', 'Rincian berhasil dihapus.');
    }

    /* =====================================================
     * AUTHORIZATION (Role: admin, kabid, atau bidang sama)
     * ===================================================== */

    private function authorizeIndex(SubKegiatan $sub): void
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['admin', 'kabid'])) {
            return;
        }

        if ((int)($sub->kegiatan->bidang_id ?? 0) === (int)($user->bidang_id ?? -1)) {
            return;
        }

        abort(403, 'Anda tidak berwenang melihat rincian subkegiatan ini.');
    }

    private function authorizeManage(SubKegiatan $sub): void
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['admin', 'kabid'])) {
            return;
        }

        if ((int)($sub->kegiatan->bidang_id ?? 0) === (int)($user->bidang_id ?? -1)) {
            return;
        }

        abort(403, 'Anda tidak berwenang mengelola rincian subkegiatan ini.');
    }
}
