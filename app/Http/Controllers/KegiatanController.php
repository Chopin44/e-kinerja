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
        $user  = Auth::user();

        // Eager load: PJ di kegiatan, sub (tanpa user_id & realisasi), rincian (tanpa realisasi)
        $query = Kegiatan::with([
        'bidang:id,nama',
        'user:id,name',
        // TIDAK pakai kategori di sub
        'subKegiatans:id,kegiatan_id,user_id,nama,target_anggaran,periode_type,tahun',
        'subKegiatans.user:id,name',
        // kategori ada di RINCIAN
        'subKegiatans.rincianKegiatans:id,sub_kegiatan_id,uraian,anggaran,kategori,satuan,volume',
        ]);



        // Filter sesuai role
        if ($user->hasRole('staf')) {
            $query->where('user_id', $user->id)
                  ->where('bidang_id', $user->bidang_id);
        } elseif ($user->hasRole('pimpinan')) {
            $query->where('bidang_id', $user->bidang_id);
        } elseif ($user->hasRole('admin')) {
            if ($request->filled('bidang_id')) {
                $query->where('bidang_id', $request->bidang_id);
            }
        }

        // Tahun
        if ($request->filled('tahun')) {
            $query->byTahun($request->tahun);
        } else {
            $query->byTahun(Carbon::now()->year);
        }

        // Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $kegiatans = $query->paginate(10);
        $bidangs   = Bidang::active()->get();

        return view('kegiatan.index', compact('kegiatans', 'bidangs'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $bidangs = Bidang::active()->get();
            $users   = User::active()->with('bidang:id,nama')->get();

            // Kegiatan yang bisa dipilih (misal tahun ini); sesuaikan filter
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

        $mode = $request->input('mode','baru');

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
            'subkegiatans'                        => ['nullable','array'],
            'subkegiatans.*.nama'                 => ['required_with:subkegiatans','string','max:255'],
            'subkegiatans.*.kode_subkegiatan'     => ['nullable','string','max:255'],
            'subkegiatans.*.user_id'          => ['nullable','exists:users,id'],
            'subkegiatans.*.target_anggaran'      => ['required_with:subkegiatans','numeric','min:0'],
            'subkegiatans.*.periode_type'         => ['nullable','in:triwulan 1,triwulan 2,triwulan 3,triwulan 4'],
            'subkegiatans.*.tahun'                => ['nullable','integer','min:2020','max:2100'],
            'subkegiatans.*.deskripsi'            => ['nullable','string'],
            'subkegiatans.*.rincian'              => ['nullable','array'],
            'subkegiatans.*.rincian.*.uraian'     => ['required_with:subkegiatans.*.rincian','string','max:255'],
            'subkegiatans.*.rincian.*.kategori'   => ['nullable','in:pengadaan_langsung,swakelola,pokir'],
            'subkegiatans.*.rincian.*.anggaran'   => ['nullable','numeric','min:0'],
            'subkegiatans.*.rincian.*.satuan'     => ['nullable','string','max:50'],
            'subkegiatans.*.rincian.*.volume'     => ['nullable','integer','min:0'],
        ];

        $validated = $request->validate(
            $mode === 'existing'
            ? array_merge($rulesKegiatanExisting, $rulesSubRinci)
            : array_merge($rulesKegiatanBaru, $rulesSubRinci)
        );

        // Override non-admin
        if (!$auth->hasRole('admin')) {
            $validated['bidang_id'] = $auth->bidang_id ?? ($validated['bidang_id'] ?? null);
            $validated['user_id']   = $auth->id       ?? ($validated['user_id'] ?? null);
        }

        DB::transaction(function () use ($validated, $mode) {
            if ($mode === 'existing') {
                // Pakai kegiatan yang sudah ada
                $kegiatan = Kegiatan::findOrFail($validated['existing_kegiatan_id']);
            } else {
                // Buat Kegiatan baru
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
                    'kode_subkegiatan' => $sub['kode_subkegiatan'] ?? null,
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
                        'satuan'          => $rinci['satuan'] ?? null,
                        'volume'          => $rinci['volume'] ?? null,
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

        $bidangs = Bidang::active()->get();
        $users   = User::active()->with('bidang:id,nama')->get();
        $kegiatan->load('subKegiatans.rincianKegiatans');

        return view('kegiatan.edit', compact('kegiatan', 'bidangs', 'users'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $this->authorize('update', $kegiatan);

        // Catatan: di level kegiatan tidak ada target_anggaran
        $validated = $request->validate([
            'nama'            => ['required','string','max:255'],
            'deskripsi'       => ['nullable','string'],
            'bidang_id'       => ['required','exists:bidangs,id'],
            'user_id'         => ['required','exists:users,id'],
            'periode_type'    => ['required','in:triwulan 1,triwulan 2,triwulan 3,triwulan 4'],
            'tanggal_mulai'   => ['required','date'],
            'tanggal_selesai' => ['required','date','after_or_equal:tanggal_mulai'],
            'tahun'           => ['required','integer','min:2020','max:2100'],
        ]);

        $kegiatan->update($validated);

        // Update nested sub/rincian sebaiknya lewat endpoint khusus.
        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil diperbarui!');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $this->authorize('delete', $kegiatan);

        $kegiatan->delete();

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil dihapus!');
    }
}
