<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\RincianKegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KegiatanController extends Controller
{
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

        // Hitung total realisasi per sub
        $realisasiPerSub = \App\Models\RealisasiRincian::selectRaw('rincian_kegiatans.sub_kegiatan_id, SUM(realisasi_rincians.realisasi_anggaran) as total_realisasi')
            ->join('rincian_kegiatans', 'rincian_kegiatans.id', '=', 'realisasi_rincians.rincian_kegiatan_id')
            ->groupBy('rincian_kegiatans.sub_kegiatan_id')
            ->pluck('total_realisasi', 'rincian_kegiatans.sub_kegiatan_id');

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Role-based data loading
        if ($user->hasRole('staf')) {
            $query->where('bidang_id', $user->bidang_id)
                ->whereHas('subKegiatans', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->with([
                    'bidang:id,nama',
                    'subKegiatans' => function ($q) use ($user) {
                        $q->select('id','kegiatan_id','user_id','nama',
                                'target_anggaran','target_fisik',
                                'realisasi_anggaran','realisasi_fisik',
                                'periode_type','tahun')
                        ->where('user_id', $user->id)
                        ->with([
                            'user:id,name',
                            'rincianKegiatans:id,sub_kegiatan_id,uraian,anggaran,target_fisik,kategori',
                        ]);
                    },
                ]);
        } elseif ($user->hasRole('pimpinan')) {
            $query->where('bidang_id', $user->bidang_id)
                ->with([
                    'bidang:id,nama',
                    'subKegiatans:id,kegiatan_id,user_id,nama,target_anggaran,target_fisik,realisasi_anggaran,realisasi_fisik,periode_type,tahun',
                    'subKegiatans.user:id,name',
                    'subKegiatans.rincianKegiatans:id,sub_kegiatan_id,uraian,anggaran,target_fisik,kategori',
                ]);
        } else { // admin
            if ($request->filled('bidang_id')) {
                $query->where('bidang_id', $request->bidang_id);
            }

            $query->with([
                'bidang:id,nama',
                'subKegiatans:id,kegiatan_id,user_id,nama,target_anggaran,target_fisik,realisasi_anggaran,realisasi_fisik,periode_type,tahun',
                'subKegiatans.user:id,name',
                'subKegiatans.rincianKegiatans:id,sub_kegiatan_id,uraian,anggaran,target_fisik,kategori',
            ]);
        }

        $kegiatans = $query->orderBy('nama')->paginate(10);
        $bidangs   = Bidang::active()->get();

        return view('kegiatan.index', compact('kegiatans', 'bidangs', 'realisasiPerSub'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $bidangs = Bidang::active()->get();
            $users   = User::active()->with('bidang:id,nama')->get();

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
            'target_fisik'    => ['nullable','numeric','min:0','max:100'],
        ];

        $rulesKegiatanExisting = [
            'existing_kegiatan_id' => ['required','exists:kegiatans,id'],
        ];

        $rulesSubRinci = [
            'subkegiatans'                          => ['nullable','array'],
            'subkegiatans.*.nama'                   => ['required_with:subkegiatans','string','max:255'],
            'subkegiatans.*.user_id'                => ['nullable','exists:users,id'],
            'subkegiatans.*.target_anggaran'        => ['required_with:subkegiatans','numeric','min:0'],
            'subkegiatans.*.target_fisik'           => ['required_with:subkegiatans','numeric','min:0','max:100'],
            'subkegiatans.*.periode_type'           => ['nullable','in:triwulan 1,triwulan 2,triwulan 3,triwulan 4'],
            'subkegiatans.*.tahun'                  => ['nullable','integer','min:2020','max:2100'],
            'subkegiatans.*.deskripsi'              => ['nullable','string'],
            'subkegiatans.*.rincian'                => ['nullable','array'],
            'subkegiatans.*.rincian.*.uraian'       => ['required_with:subkegiatans.*.rincian','string','max:255'],
            'subkegiatans.*.rincian.*.kategori'     => ['nullable','in:pengadaan_langsung,swakelola,pokir'],
            'subkegiatans.*.rincian.*.anggaran'     => ['nullable','numeric','min:0'],
            'subkegiatans.*.rincian.*.target_fisik' => ['nullable','numeric','min:0','max:100'],
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
                $kegiatan = Kegiatan::findOrFail($validated['existing_kegiatan_id']);
            } else {
                $kegiatan = Kegiatan::create([
                    'nama'             => $validated['nama'],
                    'deskripsi'        => $validated['deskripsi'] ?? null,
                    'bidang_id'        => $validated['bidang_id'],
                    'user_id'          => $validated['user_id'],
                    'periode_type'     => $validated['periode_type'],
                    'tanggal_mulai'    => $validated['tanggal_mulai'],
                    'tanggal_selesai'  => $validated['tanggal_selesai'],
                    'tahun'            => $validated['tahun'],
                    'target_fisik'     => $validated['target_fisik'] ?? 0,
                    'status'           => 'aktif',
                ]);
            }

            // Subkegiatan + rincian
            foreach (($validated['subkegiatans'] ?? []) as $sub) {
                $subModel = SubKegiatan::create([
                    'kegiatan_id'      => $kegiatan->id,
                    'user_id'          => $sub['user_id'] ?? Auth::id(),
                    'nama'             => $sub['nama'],
                    'deskripsi'        => $sub['deskripsi'] ?? null,
                    'target_anggaran'  => $sub['target_anggaran'],
                    'target_fisik'     => $sub['target_fisik'] ?? 0,
                    'periode_type'     => $sub['periode_type'] ?? $kegiatan->periode_type,
                    'tahun'            => $sub['tahun'] ?? $kegiatan->tahun,
                ]);

                foreach (($sub['rincian'] ?? []) as $rinci) {
                    RincianKegiatan::create([
                        'sub_kegiatan_id' => $subModel->id,
                        'uraian'          => $rinci['uraian'],
                        'kategori'        => $rinci['kategori'] ?? null,
                        'anggaran'        => $rinci['anggaran'] ?? null,
                        'target_fisik'    => $rinci['target_fisik'] ?? 0,
                    ]);
                }
            }
        });

        return redirect()->route('kegiatan.index')->with('success', 'Data kegiatan berhasil disimpan.');
    }

    public function show(Kegiatan $kegiatan)
    {
        $kegiatan->load([
            'bidang',
            'user',
            'subKegiatans.rincianKegiatans',
        ]);

        // ✅ Total target dari semua sub-kegiatan
        $totalTargetAnggaran = $kegiatan->subKegiatans->sum('target_anggaran');
        $totalTargetFisik = $kegiatan->subKegiatans->avg('target_fisik') ?? 0;

        // ✅ Realisasi dari realisasi_rincians
        $totalRealisasiAnggaran = \App\Models\RealisasiRincian::join('rincian_kegiatans', 'rincian_kegiatans.id', '=', 'realisasi_rincians.rincian_kegiatan_id')
            ->join('sub_kegiatans', 'sub_kegiatans.id', '=', 'rincian_kegiatans.sub_kegiatan_id')
            ->where('sub_kegiatans.kegiatan_id', $kegiatan->id)
            ->sum('realisasi_rincians.realisasi_anggaran');

        $totalRealisasiFisik = \App\Models\RealisasiRincian::join('rincian_kegiatans', 'rincian_kegiatans.id', '=', 'realisasi_rincians.rincian_kegiatan_id')
            ->join('sub_kegiatans', 'sub_kegiatans.id', '=', 'rincian_kegiatans.sub_kegiatan_id')
            ->where('sub_kegiatans.kegiatan_id', $kegiatan->id)
            ->avg('realisasi_rincians.realisasi_fisik') ?? 0;

        // ✅ Progress
        $budgetProgress = $totalTargetAnggaran > 0
            ? ($totalRealisasiAnggaran / $totalTargetAnggaran) * 100
            : 0;

        $fisikProgress = $totalTargetFisik > 0
            ? ($totalRealisasiFisik / $totalTargetFisik) * 100
            : 0;

        return view('kegiatan.show', compact(
            'kegiatan',
            'totalTargetAnggaran',
            'totalTargetFisik',
            'totalRealisasiAnggaran',
            'totalRealisasiFisik',
            'budgetProgress',
            'fisikProgress'
        ));
    }





    public function edit(Kegiatan $kegiatan)
    {
        $auth = Auth::user();

        // Manual authorization
        if (!$auth->hasAnyRole(['admin', 'kabid']) && $auth->id !== $kegiatan->user_id) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit kegiatan ini.');
        }

        if ($auth->hasRole('admin')) {
            $bidangs = Bidang::active()->get();
            $users   = User::active()->with('bidang:id,nama')->get();
        } else {
            $bidangs = Bidang::where('id', $auth->bidang_id)->get();
            $users   = User::where('id', $auth->id)->with('bidang:id,nama')->get();
        }

        return view('kegiatan.edit', compact('kegiatan', 'bidangs', 'users'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $auth = Auth::user();

        // Manual authorization
        if (!$auth->hasAnyRole(['admin', 'kabid']) && $auth->id !== $kegiatan->user_id) {
            abort(403, 'Anda tidak memiliki izin untuk mengupdate kegiatan ini.');
        }

        $validated = $request->validate([
            'nama'            => ['required','string','max:255'],
            'deskripsi'       => ['nullable','string'],
            'bidang_id'       => ['required','exists:bidangs,id'],
            'periode_type'    => ['required','in:triwulan 1,triwulan 2,triwulan 3,triwulan 4'],
            'tanggal_mulai'   => ['required','date'],
            'tanggal_selesai' => ['required','date','after_or_equal:tanggal_mulai'],
            'tahun'           => ['required','integer','min:2020','max:2100'],
            'status'          => ['required','in:draft,aktif,selesai'],
            'target_fisik'    => ['nullable','numeric','min:0','max:100'],
        ]);

        if (!$auth->hasRole('admin')) {
            $validated['bidang_id'] = $auth->bidang_id ?? $kegiatan->bidang_id;
            $validated['user_id']   = $auth->id ?? $kegiatan->user_id;
        }

        $kegiatan->update($validated);

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil diperbarui!');
    }

   public function destroy(Kegiatan $kegiatan)
    {
        $auth = Auth::user();

        if (!$auth->hasAnyRole(['admin', 'kabid']) && $auth->id !== $kegiatan->user_id) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus kegiatan ini.');
        }

        DB::transaction(function () use ($kegiatan) {
            foreach ($kegiatan->subKegiatans as $sub) {
                foreach ($sub->rincianKegiatans as $rincian) {
                    // Hapus realisasi rinciannya
                    DB::table('realisasi_rincians')->where('rincian_kegiatan_id', $rincian->id)->delete();
                }
                // Hapus rincian
                DB::table('rincian_kegiatans')->where('sub_kegiatan_id', $sub->id)->delete();
            }

            // Hapus subkegiatannya
            DB::table('sub_kegiatans')->where('kegiatan_id', $kegiatan->id)->delete();

            // Kalau ada evaluasi atau realisasi langsung di kegiatan:
            DB::table('evaluasis')->where('kegiatan_id', $kegiatan->id)->delete();
            DB::table('realisasis')->where('kegiatan_id', $kegiatan->id)->delete();

            // Terakhir hapus kegiatan
            $kegiatan->delete();
        });

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan dan semua data terkait berhasil dihapus!');
    }

}
