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
        
        // Filter berdasarkan bidang jika user adalah staf
        if (Auth::user()->role === 'staf') {
            $query->where('bidang_id', Auth::user()->bidang_id);
        }
        
        // Filter berdasarkan parameter
        if ($request->filled('bidang_id')) {
            $query->byBidang($request->bidang_id);
        }
        
        if ($request->filled('tahun')) {
            $query->byTahun($request->tahun);
        } else {
            $query->byTahun(Carbon::now()->year);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $kegiatans = $query->paginate(10);
        $bidangs = Bidang::active()->get();
        
        return view('kegiatan.index', compact('kegiatans', 'bidangs'));
    }
    
    public function create()
    {
        $bidangs = Bidang::active()->get();
        $users = User::active()->get();
        
        return view('kegiatan.create', compact('bidangs', 'users'));
    }
    
    public function store(Request $request)
    {
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
        
        Kegiatan::create($request->all());
        
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