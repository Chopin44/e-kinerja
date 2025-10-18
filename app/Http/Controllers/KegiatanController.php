<?php
// app/Http/Controllers/KegiatanController.php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\RincianKegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KegiatanController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Kegiatan::query();

        // Filter tahun
        $query->when(
            $request->filled('tahun'),
            fn ($q) => $q->byTahun($request->tahun),
            fn ($q) => $q->byTahun(Carbon::now()->year)
        );

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($user->hasRole('staf')) {
            // HANYA kegiatan di bidangnya, dan yang punya sub milik dia
            $query->where('bidang_id', $user->bidang_id)
                  ->whereHas('subKegiatans', function ($q) use ($user) {
                      $q->where('user_id', $user->id);
                  })
                  ->with([
                      'bidang:id,nama',
                      // Eager-load hanya SUB milik staf yang login + user & rincian
                      'subKegiatans' => function ($q) use ($user) {
                          $q->select('id','kegiatan_id','user_id','nama','target_anggaran','periode_type','tahun')
                            ->where('user_id', $user->id)
                            ->with([
                                'user:id,name',
                                'rincianKegiatans:id,sub_kegiatan_id,uraian,anggaran,kategori',
                            ]);
                      },
                  ]);

        } elseif ($user->hasRole('pimpinan')) {
            // Semua sub di bidangnya (monitoring)
            $query->where('bidang_id', $user->bidang_id)
                  ->with([
                      'bidang:id,nama',
                      'subKegiatans:id,kegiatan_id,user_id,nama,target_anggaran,periode_type,tahun',
                      'subKegiatans.user:id,name',
                      'subKegiatans.rincianKegiatans:id,sub_kegiatan_id,uraian,anggaran,kategori',
                  ]);

        } else { // admin
            if ($request->filled('bidang_id')) {
                $query->where('bidang_id', $request->bidang_id);
            }

            $query->with([
                'bidang:id,nama',
                'subKegiatans:id,kegiatan_id,user_id,nama,target_anggaran,periode_type,tahun',
                'subKegiatans.user:id,name',
                'subKegiatans.rincianKegiatans:id,sub_kegiatan_id,uraian,anggaran,kategori',
            ]);
        }

        $kegiatans = $query->orderBy('nama')->paginate(10);
        $bidangs   = Bidang::active()->get();

        return view('kegiatan.index', compact('kegiatans', 'bidangs'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $bidangs = Bidang::active()->get();
            $users   = User::active()->with('bidang:id,nama')->get();

            // Kegiatan existing (mis. untuk tambah sub di kegiatan yang sama)
            $kegiatansExisting = Kegiatan::with('bidang:id,nama')
                ->orderByDesc('tahun')
                ->orderBy('nama')
                ->get();
        } else {
            $bidangs = Bidang::where('id', $user->bidang_id)->get();
            $users   = collect([$user->load('bidang')]);

            $kegiatansExisting = Kegiatan::with('bidang:id,nama')
                ->where('bidang_id', $user->bidang_id)
                ->orderByDesc('tahun')
                ->orderBy('nama')
                ->get();
        }

        return view('kegiatan.create', compact('bidangs','users','kegiatansExisting'));
    }

    public function store(Request $request)
    {
        $auth = Auth::user();

        $mode = $request->input('mode', 'baru');

        // Validasi dasar berbeda untuk 2 mode
        $rulesKegiatanBaru = [
            'nama'            => ['required','string','max:255'],
            'deskripsi'       => ['nullable','string'],
            'bidang_id'       => ['nullable','exists:bidangs,id'],
            'user_id'         => ['nullable','exists:users,id'],
            'periode_type'    => ['required','in:triwulan 1,triwulan 2,triwulan 3,triwulan 4'],
            'tanggal_mulai'   => ['required','date'],
            'tanggal_selesai' => ['required','date','after_or_equal:tanggal_mulai'],
            'tahun'           => ['required','integer','min:2020','max:2100'],
        ];

        $rulesKegiatanExisting = [
            'existing_kegiatan_id' => ['required','exists:kegiatans,id'],
        ];

        $rulesSubRinci = [
            'subkegiatans'                          => ['nullable','array'],
            'subkegiatans.*.nama'                   => ['required_with:subkegiatans','string','max:255'],
            'subkegiatans.*.user_id'                => ['nullable','exists:users,id'],
            'subkegiatans.*.target_anggaran'        => ['required_with:subkegiatans','numeric','min:0'],
            'subkegiatans.*.periode_type'           => ['nullable','in:triwulan 1,triwulan 2,triwulan 3,triwulan 4'],
            'subkegiatans.*.tahun'                  => ['nullable','integer','min:2020','max:2100'],
            'subkegiatans.*.deskripsi'              => ['nullable','string'],
            'subkegiatans.*.rincian'                => ['nullable','array'],
            'subkegiatans.*.rincian.*.uraian'       => ['required_with:subkegiatans.*.rincian','string','max:255'],
            'subkegiatans.*.rincian.*.kategori'     => ['nullable','in:pengadaan_langsung,swakelola,pokir'],
            'subkegiatans.*.rincian.*.anggaran'     => ['nullable','numeric','min:0'],
        ];

        $validated = $request->validate(
            $mode === 'existing'
                ? array_merge($rulesKegiatanExisting, $rulesSubRinci)
                : array_merge($rulesKegiatanBaru, $rulesSubRinci)
        );

        // Override non-admin: kunci bidang & user ke miliknya
        if (!$auth->hasRole('admin')) {
            $validated['bidang_id'] = $auth->bidang_id ?? ($validated['bidang_id'] ?? null);
            $validated['user_id']   = $auth->id       ?? ($validated['user_id'] ?? null);
        }

        DB::transaction(function () use ($validated, $mode) {
            if ($mode === 'existing') {
                // Pakai kegiatan yang sudah ada
                $kegiatan = Kegiatan::findOrFail($validated['existing_kegiatan_id']);
            } else {
                // Buat Kegiatan baru (tanpa kategori/target di level kegiatan)
                $kegiatan = Kegiatan::create([
                    'nama'             => $validated['nama'],
                    'deskripsi'        => $validated['deskripsi'] ?? null,
                    'bidang_id'        => $validated['bidang_id'],
                    'user_id'          => $validated['user_id'],
                    'periode_type'     => $validated['periode_type'],
                    'tanggal_mulai'    => $validated['tanggal_mulai'],
                    'tanggal_selesai'  => $validated['tanggal_selesai'],
                    'tahun'            => $validated['tahun'],
                    'status'           => 'aktif',
                ]);
            }

            // Tambah Subkegiatan (+ Rincian) ke kegiatan terpilih/baru
            foreach (($validated['subkegiatans'] ?? []) as $sub) {
                $subModel = SubKegiatan::create([
                    'kegiatan_id'      => $kegiatan->id,
                    'user_id'          => $sub['user_id'] ?? Auth::id(),
                    'nama'             => $sub['nama'],
                    'deskripsi'        => $sub['deskripsi'] ?? null,
                    'target_anggaran'  => $sub['target_anggaran'],
                    'periode_type'     => $sub['periode_type'] ?? $kegiatan->periode_type,
                    'tahun'            => $sub['tahun'] ?? $kegiatan->tahun,
                ]);

                foreach (($sub['rincian'] ?? []) as $rinci) {
                    RincianKegiatan::create([
                        'sub_kegiatan_id' => $subModel->id,
                        'uraian'          => $rinci['uraian'],
                        'kategori'        => $rinci['kategori'] ?? null,
                        'anggaran'        => $rinci['anggaran'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('kegiatan.index')->with('success', 'Data tersimpan.');
    }

    public function show(Kegiatan $kegiatan)
    {
        $kegiatan->load([
            'bidang', 'user',
            'subKegiatans.rincianKegiatans',
            'realisasis.dokumens',
            'evaluasis.evaluator',
        ]);

        return view('kegiatan.show', compact('kegiatan'));
    }

    public function edit(Kegiatan $kegiatan)
    {
        $this->authorize('update', $kegiatan);

        $auth = Auth::user();

        if ($auth->hasRole('admin')) {
            // Admin boleh pilih bidang & user manapun
            $bidangs = Bidang::active()->get();
            $users   = User::active()->with('bidang:id,nama')->get();
        } else {
            // Non-admin: kunci ke bidang & user dirinya
            $bidangs = Bidang::where('id', $auth->bidang_id)->get();
            $users   = User::where('id', $auth->id)->with('bidang:id,nama')->get();
        }

        // Tidak perlu load sub/rincian di halaman edit kegiatan (mereka dikelola di halaman khusus)
        return view('kegiatan.edit', compact('kegiatan', 'bidangs', 'users'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $this->authorize('update', $kegiatan);

        // Validasi hanya field di level kegiatan
        $validated = $request->validate([
            'nama'            => ['required','string','max:255'],
            'deskripsi'       => ['nullable','string'],
            'bidang_id'       => ['required','exists:bidangs,id'],
            'periode_type'    => ['required','in:triwulan 1,triwulan 2,triwulan 3,triwulan 4'],
            'tanggal_mulai'   => ['required','date'],
            'tanggal_selesai' => ['required','date','after_or_equal:tanggal_mulai'],
            'tahun'           => ['required','integer','min:2020','max:2100'],
            'status'          => ['required','in:draft,aktif,selesai'],
        ]);

        // Non-admin tidak boleh memindah bidang/user semaunya — paksa ke miliknya
        $auth = Auth::user();
        if (!$auth->hasRole('admin')) {
            $validated['bidang_id'] = $auth->bidang_id ?? $kegiatan->bidang_id;
            $validated['user_id']   = $auth->id       ?? $kegiatan->user_id;
        }

        $kegiatan->update([
            'nama'            => $validated['nama'],
            'deskripsi'       => $validated['deskripsi'] ?? null,
            'bidang_id'       => $validated['bidang_id'],
            'periode_type'    => $validated['periode_type'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'tahun'           => $validated['tahun'],
            'status'          => $validated['status'],
        ]);

        return redirect()
            ->route('kegiatan.index')
            ->with('success', 'Kegiatan berhasil diperbarui!');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $this->authorize('delete', $kegiatan);

        $kegiatan->delete();

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil dihapus!');
    }
}
