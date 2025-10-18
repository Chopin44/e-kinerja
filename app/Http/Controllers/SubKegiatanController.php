<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\RincianKegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubKegiatanController extends Controller
{
    /**
     * Tampilkan form create Subkegiatan.
     * Opsional query string: ?kegiatan_id=xx untuk preselect kegiatan.
     */
    public function create(Request $request)
    {
        $auth = Auth::user();
        $preselected = null;

        // Admin/Kabid/Pimpinan boleh memilih dari semua kegiatan (opsional bisa dibatasi per bidang)
        if ($auth->hasRole(['admin','kabid','pimpinan'])) {
            $kegiatans = Kegiatan::with('bidang:id,nama')
                ->orderByDesc('tahun')
                ->orderBy('nama')
                ->get();

        } else { // staf: hanya kegiatan di bidangnya
            $kegiatans = Kegiatan::with('bidang:id,nama')
                ->where('bidang_id', $auth->bidang_id)
                ->orderByDesc('tahun')
                ->orderBy('nama')
                ->get();
        }

        if ($request->filled('kegiatan_id')) {
            $preselected = (int) $request->kegiatan_id;
        }

        // User (staf admin) yang bisa dipilih untuk menjadi penanggung jawab subkegiatan
        // Admin/kabid/pimpinan: semua user aktif (opsional batasi per bidang kegiatan terpilih via JS di form)
        // Staf: hanya dirinya sendiri (tidak bisa pilih orang lain)
        if ($auth->hasRole(['admin','kabid','pimpinan'])) {
            $users = User::active()->with('bidang:id,nama')->get();
        } else {
            $users = collect([$auth->load('bidang')]);
        }

        return view('subkegiatan.create', compact('kegiatans','users','preselected'));
    }

    /**
     * Simpan Subkegiatan baru.
     */
    public function store(Request $request)
    {
        $auth = Auth::user();

        $validated = $request->validate([
            'kegiatan_id'     => ['required','exists:kegiatans,id'],
            'user_id'         => ['nullable','exists:users,id'],
            'nama'            => ['required','string','max:255'],
            'deskripsi'       => ['nullable','string'],
            'target_anggaran' => ['required','numeric','min:0'],
            'periode_type'    => ['nullable','in:triwulan 1,triwulan 2,triwulan 3,triwulan 4'],
            'tahun'           => ['nullable','integer','min:2020','max:2100'],
        ]);

        $kegiatan = Kegiatan::findOrFail($validated['kegiatan_id']);

        // Staf hanya boleh menambah sub pada kegiatan di bidangnya
        if ($auth->hasRole('staf')) {
            abort_unless($kegiatan->bidang_id === $auth->bidang_id, 403, 'Tidak boleh membuat subkegiatan di luar bidang Anda.');
        }

        $sub = SubKegiatan::create([
            'kegiatan_id'     => $kegiatan->id,
            'user_id'         => $validated['user_id'] ?? $auth->id,
            'nama'            => $validated['nama'],
            'deskripsi'       => $validated['deskripsi'] ?? null,
            'target_anggaran' => $validated['target_anggaran'],
            'periode_type'    => $validated['periode_type'] ?? $kegiatan->periode_type,
            'tahun'           => $validated['tahun'] ?? $kegiatan->tahun,
        ]);

        return redirect()
            ->route('kegiatan.index')
            ->with('success', 'Subkegiatan berhasil dibuat.');
    }

    /**
     * (Opsional) detail subkegiatan.
     */
    public function show(SubKegiatan $subkegiatan)
    {
        $this->authorizeSub($subkegiatan);

        $subkegiatan->load(['kegiatan.bidang','user','rincianKegiatans']);
        return view('subkegiatan.show', compact('subkegiatan'));
    }

    /**
     * Form edit Subkegiatan.
     */
    public function edit(SubKegiatan $subkegiatan)
    {
        $this->authorizeSub($subkegiatan);

        $auth = Auth::user();

        // Pilihan user: admin/kabid/pimpinan bisa pilih siapa saja, staf hanya dirinya sendiri
        if ($auth->hasRole(['admin','kabid','pimpinan'])) {
            // Opsional: batasi user per bidang kegiatan sub
            $users = User::active()
                ->with('bidang:id,nama')
                ->get();
        } else { // staf
            $users = collect([$auth->load('bidang')]);
        }

        // Kegiatan list (jika ingin pindahkan sub ke kegiatan lain)
        if ($auth->hasRole(['admin','kabid','pimpinan'])) {
            $kegiatans = Kegiatan::with('bidang:id,nama')
                ->orderByDesc('tahun')->orderBy('nama')->get();
        } else {
            $kegiatans = Kegiatan::with('bidang:id,nama')
                ->where('bidang_id', $auth->bidang_id)
                ->orderByDesc('tahun')->orderBy('nama')->get();
        }

        return view('subkegiatan.edit', compact('subkegiatan','users','kegiatans'));
    }

    /**
     * Update Subkegiatan.
     */
    public function update(Request $request, SubKegiatan $subkegiatan)
    {
        $this->authorizeSub($subkegiatan);

        $validated = $request->validate([
            'kegiatan_id'     => ['required','exists:kegiatans,id'],
            'user_id'         => ['nullable','exists:users,id'],
            'nama'            => ['required','string','max:255'],
            'deskripsi'       => ['nullable','string'],
            'target_anggaran' => ['required','numeric','min:0'],
            'periode_type'    => ['nullable','in:triwulan 1,triwulan 2,triwulan 3,triwulan 4'],
            'tahun'           => ['nullable','integer','min:2020','max:2100'],
        ]);

        $kegiatanBaru = Kegiatan::findOrFail($validated['kegiatan_id']);
        $auth = Auth::user();

        // Staf tidak boleh memindahkan sub keluar dari bidangnya
        if ($auth->hasRole('staf')) {
            abort_unless($kegiatanBaru->bidang_id === $auth->bidang_id, 403, 'Tidak boleh memindahkan subkegiatan ke bidang lain.');
            // Staf juga tidak boleh mengganti penanggung jawab ke orang lain
            $validated['user_id'] = $subkegiatan->user_id; // atau paksa tetap auth->id
        }

        $subkegiatan->update([
            'kegiatan_id'     => $kegiatanBaru->id,
            'user_id'         => $validated['user_id'] ?? $subkegiatan->user_id,
            'nama'            => $validated['nama'],
            'deskripsi'       => $validated['deskripsi'] ?? null,
            'target_anggaran' => $validated['target_anggaran'],
            'periode_type'    => $validated['periode_type'] ?? $kegiatanBaru->periode_type,
            'tahun'           => $validated['tahun'] ?? $kegiatanBaru->tahun,
        ]);

        return redirect()
            ->route('kegiatan.index')
            ->with('success', 'Subkegiatan berhasil diperbarui.');
    }

    /**
     * Hapus Subkegiatan + (otomatis) semua rincian di dalamnya (jika FK cascade).
     */
    public function destroy(SubKegiatan $subkegiatan)
    {
        $this->authorizeSub($subkegiatan);

        $subkegiatan->delete();

        return back()->with('success', 'Subkegiatan berhasil dihapus.');
    }

    /**
     * Guard otorisasi untuk SubKegiatan:
     * - admin/kabid/pimpinan: boleh
     * - staf: hanya jika sub.user_id == dirinya
     */
    private function authorizeSub(SubKegiatan $sub): void
    {
        $user = Auth::user();

        if ($user->hasRole(['admin','kabid','pimpinan'])) {
            return;
        }

        if ($user->hasRole('staf') && (int)$sub->user_id === (int)$user->id) {
            return;
        }

        abort(403, 'Anda tidak berwenang mengelola subkegiatan ini.');
    }
}
