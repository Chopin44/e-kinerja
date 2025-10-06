<?php

namespace App\Http\Controllers;

use App\Models\Evaluasi;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EvaluasiController extends Controller
{
    /**
     * Tampilkan daftar evaluasi (admin only) dengan filter:
     * - kegiatan_id
     * - status_evaluasi
     * - tanggal_evaluasi (rentang: date_from - date_to)
     */
    public function index(Request $request)
    {
        // opsional: pastikan hanya admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengakses evaluasi.');
        }

        $query = Evaluasi::query()
            ->with(['kegiatan.bidang', 'evaluator'])
            ->orderByDesc('tanggal_evaluasi')
            ->orderByDesc('id');

        // Filter
        if ($request->filled('kegiatan_id')) {
            $query->where('kegiatan_id', $request->kegiatan_id);
        }

        if ($request->filled('status_evaluasi')) {
            $query->where('status_evaluasi', $request->status_evaluasi);
        }

        // Rentang tanggal
        $from = $request->get('date_from');
        $to   = $request->get('date_to');
        if ($from && $to) {
            $query->whereBetween('tanggal_evaluasi', [$from, $to]);
        } elseif ($from) {
            $query->whereDate('tanggal_evaluasi', '>=', $from);
        } elseif ($to) {
            $query->whereDate('tanggal_evaluasi', '<=', $to);
        }

        $evaluasis = $query->paginate(12)->withQueryString();

        // dropdown kegiatan (opsional: hanya aktif)
        $kegiatans = Kegiatan::orderBy('nama')->get();

        return view('evaluasi.index', compact('evaluasis', 'kegiatans'));
    }

    /**
     * Form buat evaluasi (admin only)
     */
    public function create(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $kegiatans = Kegiatan::orderBy('nama')->get();

        // Boleh preselect kegiatan via query ?kegiatan_id=...
        $selectedKegiatanId = $request->get('kegiatan_id');

        return view('evaluasi.create', [
            'kegiatans' => $kegiatans,
            'selectedKegiatanId' => $selectedKegiatanId,
        ]);
    }

    /**
     * Simpan evaluasi (admin only)
     */
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $data = $request->validate([
            'kegiatan_id'      => ['required', Rule::exists('kegiatans', 'id')],
            'status_evaluasi'  => ['required', Rule::in(['on_track', 'terlambat', 'tidak_sesuai'])],
            'catatan_evaluasi' => ['required', 'string', 'min:10'],
            'rekomendasi'      => ['nullable', 'string'],
            'tanggal_evaluasi' => ['required', 'date'],
        ]);

        $data['evaluator_id'] = Auth::id();

        Evaluasi::create($data);

        // Redirect ke detail kegiatan atau ke index evaluasi (pilih salah satu, di sini ke index)
        return redirect()
            ->route('evaluasi.index')
            ->with('success', 'Evaluasi berhasil ditambahkan.');
    }

    /**
     * Detail evaluasi (admin only)
     */
    public function show(Evaluasi $evaluasi)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $evaluasi->load(['kegiatan.bidang', 'evaluator']);

        return view('evaluasi.show', compact('evaluasi'));
    }

    /**
     * Form edit evaluasi (admin only)
     */
    public function edit(Evaluasi $evaluasi)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $evaluasi->load(['kegiatan', 'evaluator']);
        $kegiatans = Kegiatan::orderBy('nama')->get();

        return view('evaluasi.edit', compact('evaluasi', 'kegiatans'));
    }

    /**
     * Update evaluasi (admin only)
     */
    public function update(Request $request, Evaluasi $evaluasi)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $data = $request->validate([
            'kegiatan_id'      => ['required', Rule::exists('kegiatans', 'id')],
            'status_evaluasi'  => ['required', Rule::in(['on_track', 'terlambat', 'tidak_sesuai'])],
            'catatan_evaluasi' => ['required', 'string', 'min:10'],
            'rekomendasi'      => ['nullable', 'string'],
            'tanggal_evaluasi' => ['required', 'date'],
        ]);

        // evaluator_id tidak diubah (pembuat awal), tapi kalau mau ganti ke editor saat ini bisa:
        // $data['evaluator_id'] = Auth::id();

        $evaluasi->update($data);

        return redirect()
            ->route('evaluasi.index')
            ->with('success', 'Evaluasi berhasil diperbarui.');
    }

    /**
     * Hapus evaluasi (admin only)
     */
    public function destroy(Evaluasi $evaluasi)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $evaluasi->delete();

        return redirect()
            ->route('evaluasi.index')
            ->with('success', 'Evaluasi berhasil dihapus.');
    }
}
