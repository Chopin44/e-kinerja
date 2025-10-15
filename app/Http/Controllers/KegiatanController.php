<?php
// app/Http/Controllers/KegiatanController.php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class KegiatanController extends Controller
{
    use AuthorizesRequests;
    
    public function index(Request $request)
    {
        $query = Kegiatan::with(['bidang', 'user', 'realisasis']);
        $user = Auth::user();

        // 🔒 Filter kegiatan sesuai role
        if ($user->hasRole('staf')) {
            // Staf hanya bisa melihat kegiatan miliknya sendiri
            $query->where('user_id', $user->id)
                ->where('bidang_id', $user->bidang_id);

        } elseif ($user->hasRole('pimpinan')) {
            // Pimpinan bisa melihat semua kegiatan di bidangnya
            $query->where('bidang_id', $user->bidang_id);

        } elseif ($user->hasRole('admin')) {
            // Admin bisa melihat semua kegiatan, tapi boleh filter manual
            if ($request->filled('bidang_id')) {
                $query->where('bidang_id', $request->bidang_id);
            }
        }

        // 🗓️ Filter tahun
        if ($request->filled('tahun')) {
            $query->byTahun($request->tahun);
        } else {
            $query->byTahun(Carbon::now()->year);
        }

        // 📊 Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $kegiatans = $query->paginate(10);
        $bidangs = Bidang::active()->get();

        return view('kegiatan.index', compact('kegiatans', 'bidangs'));
    }


    
    public function create()
    {
        $user = Auth::user();

        // Admin bisa lihat semua
        if ($user->hasRole('admin')) {
            $bidangs = Bidang::active()->get();
            $users = User::active()->get();
        } 
        // Non-admin hanya lihat bidang & user-nya sendiri
        else {
            $bidangs = Bidang::where('id', $user->bidang_id)->get();
            $users = collect([$user]); // hanya dirinya sendiri
        }

        return view('kegiatan.create', compact('bidangs', 'users'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'bidang_id' => 'nullable|exists:bidangs,id',
            'user_id' => 'nullable|exists:users,id',
            'kategori' => 'required|in:pengadaan_langsung,swakelola,pokir',
            'periode_type' => 'required|in:triwulan 1,triwulan 2,triwulan 3, triwulan 4',
            'target_fisik' => 'required|numeric|min:0|max:100',
            'target_anggaran' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tahun' => 'required|integer|min:2020|max:2030',
        ]);

        // ✨ Otomatis override untuk non-admin
        $data = $request->all();
        if (!$user->hasRole('admin')) {
            $data['bidang_id'] = $user->bidang_id;
            $data['user_id'] = $user->id;
        }

        Kegiatan::create($data);

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil ditambahkan!');
    }

        
    public function show(Kegiatan $kegiatan)
    {
        $kegiatan->load(['bidang', 'user', 'realisasis.dokumens', 'evaluasis.evaluator']);
        
        return view('kegiatan.show', compact('kegiatan'));
    }
    
    public function edit(Kegiatan $kegiatan)
    {
        $this->authorize('update', $kegiatan);
        
        $bidangs = Bidang::active()->get();
        $users = User::active()->get();
        
        return view('kegiatan.edit', compact('kegiatan', 'bidangs', 'users'));
    }
    
    public function update(Request $request, Kegiatan $kegiatan)
    {
        $this->authorize('update', $kegiatan);
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'bidang_id' => 'required|exists:bidangs,id',
            'user_id' => 'required|exists:users,id',
            'kategori' => 'required|in:pengadaan_langsung,swakelola,pokir',
            'periode_type' => 'required|in:tahunan,bulanan,triwulan',
            'target_fisik' => 'required|numeric|min:0|max:100',
            'target_anggaran' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'tahun' => 'required|integer|min:2020|max:2030',
        ]);
        
        $kegiatan->update($request->all());
        
        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil diperbarui!');
    }
    
    public function destroy(Kegiatan $kegiatan)
    {
        $this->authorize('delete', $kegiatan);
        
        $kegiatan->delete();
        
        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil dihapus!');
    }
}