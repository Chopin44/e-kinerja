<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubKegiatanController extends Controller
{
    /**
     * Tampilkan form create Subkegiatan.
     */
    public function create(Request $request)
    {
        $auth = Auth::user();
        $preselected = $request->integer('kegiatan_id');

        // Admin/kabid/pimpinan: semua kegiatan
        if ($auth->hasAnyRole(['admin', 'kabid', 'pimpinan'])) {
            $kegiatans = Kegiatan::with('bidang:id,nama')
                ->orderByDesc('tahun')
                ->orderBy('nama')
                ->get();
        } else {
            // staf: hanya kegiatan di bidangnya
            $kegiatans = Kegiatan::with('bidang:id,nama')
                ->where('bidang_id', $auth->bidang_id)
                ->orderByDesc('tahun')
                ->orderBy('nama')
                ->get();
        }

        // User list
        if ($auth->hasAnyRole(['admin', 'kabid', 'pimpinan'])) {
            $users = User::active()->with('bidang:id,nama')->get();
        } else {
            $users = collect([$auth->load('bidang')]);
        }

        return view('subkegiatan.create', compact('kegiatans', 'users', 'preselected'));
    }

    /**
     * Simpan Subkegiatan baru.
     */
    public function store(Request $request)
    {
        $auth = Auth::user();

        $validated = $request->validate([
            'kegiatan_id'     => ['required', 'exists:kegiatans,id'],
            'user_id'         => ['nullable', 'exists:users,id'],
            'nama'            => ['required', 'string', 'max:255'],
            'deskripsi'       => ['nullable', 'string'],
            'target_anggaran' => ['required', 'numeric', 'min:0'],
            'target_fisik'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'periode_type'    => ['nullable', 'in:triwulan 1,triwulan 2,triwulan 3,triwulan 4'],
            'tahun'           => ['nullable', 'integer', 'min:2020', 'max:2100'],
        ]);

        $kegiatan = Kegiatan::findOrFail($validated['kegiatan_id']);

        if ($auth->hasRole('staf')) {
            abort_unless($kegiatan->bidang_id === $auth->bidang_id, 403, 'Tidak boleh membuat subkegiatan di luar bidang Anda.');
        }

        SubKegiatan::create([
            'kegiatan_id'     => $kegiatan->id,
            'user_id'         => $validated['user_id'] ?? $auth->id,
            'nama'            => $validated['nama'],
            'deskripsi'       => $validated['deskripsi'] ?? null,
            'target_anggaran' => $validated['target_anggaran'],
            'target_fisik'    => $validated['target_fisik'] ?? 0,
            'periode_type'    => $validated['periode_type'] ?? $kegiatan->periode_type,
            'tahun'           => $validated['tahun'] ?? $kegiatan->tahun,
        ]);

        return redirect()
            ->route('kegiatan.index')
            ->with('success', 'Subkegiatan berhasil dibuat.');
    }

    /**
     * Form edit Subkegiatan.
     */
    public function edit(SubKegiatan $subkegiatan)
    {
        $this->authorizeSub($subkegiatan);

        $auth = Auth::user();

        if ($auth->hasAnyRole(['admin', 'kabid', 'pimpinan'])) {
            $users = User::active()->with('bidang:id,nama')->get();
            $kegiatans = Kegiatan::with('bidang:id,nama')
                ->orderByDesc('tahun')
                ->orderBy('nama')
                ->get();
        } else {
            $users = collect([$auth->load('bidang')]);
            $kegiatans = Kegiatan::with('bidang:id,nama')
                ->where('bidang_id', $auth->bidang_id)
                ->orderByDesc('tahun')
                ->orderBy('nama')
                ->get();
        }

        return view('subkegiatan.edit', compact('subkegiatan', 'users', 'kegiatans'));
    }

    /**
     * Update Subkegiatan.
     */
    public function update(Request $request, SubKegiatan $subkegiatan)
    {
        $this->authorizeSub($subkegiatan);

        $validated = $request->validate([
            'kegiatan_id'     => ['required', 'exists:kegiatans,id'],
            'user_id'         => ['nullable', 'exists:users,id'],
            'nama'            => ['required', 'string', 'max:255'],
            'deskripsi'       => ['nullable', 'string'],
            'target_anggaran' => ['required', 'numeric', 'min:0'],
            'target_fisik'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'periode_type'    => ['nullable', 'in:triwulan 1,triwulan 2,triwulan 3,triwulan 4'],
            'tahun'           => ['nullable', 'integer', 'min:2020', 'max:2100'],
        ]);

        $kegiatanBaru = Kegiatan::findOrFail($validated['kegiatan_id']);
        $auth = Auth::user();

        if ($auth->hasRole('staf')) {
            abort_unless($kegiatanBaru->bidang_id === $auth->bidang_id, 403, 'Tidak boleh memindahkan subkegiatan ke bidang lain.');
            $validated['user_id'] = $subkegiatan->user_id;
        }

        $subkegiatan->update([
            'kegiatan_id'     => $kegiatanBaru->id,
            'user_id'         => $validated['user_id'] ?? $subkegiatan->user_id,
            'nama'            => $validated['nama'],
            'deskripsi'       => $validated['deskripsi'] ?? null,
            'target_anggaran' => $validated['target_anggaran'],
            'target_fisik'    => $validated['target_fisik'] ?? $subkegiatan->target_fisik,
            'periode_type'    => $validated['periode_type'] ?? $kegiatanBaru->periode_type,
            'tahun'           => $validated['tahun'] ?? $kegiatanBaru->tahun,
        ]);

        return redirect()
            ->route('kegiatan.index')
            ->with('success', 'Subkegiatan berhasil diperbarui.');
    }

    /**
     * Hapus Subkegiatan.
     */
    public function destroy(SubKegiatan $subkegiatan)
    {
        $this->authorizeSub($subkegiatan);
        $subkegiatan->delete();

        return back()->with('success', 'Subkegiatan berhasil dihapus.');
    }

    /**
     * Guard otorisasi.
     */
    private function authorizeSub(SubKegiatan $sub): void
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['admin', 'kabid', 'pimpinan'])) {
            return;
        }

        if ($user->hasRole('staf') && (int)$sub->user_id === (int)$user->id) {
            return;
        }

        abort(403, 'Anda tidak berwenang mengelola subkegiatan ini.');
    }
}
