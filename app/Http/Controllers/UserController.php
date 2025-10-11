<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('bidang')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $bidangs = Bidang::active()->get();
        return view('users.create', compact('bidangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'username' => 'required|string|unique:users,username',
            'bidang_id' => 'nullable|exists:bidangs,id',
            'role' => 'required|in:admin,staf,pimpinan',
            'password' => 'required|min:6|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'phone' => $request->phone,
            'username' => $request->username,
            'bidang_id' => $request->bidang_id,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $bidangs = Bidang::active()->get();
        return view('users.edit', compact('user', 'bidangs'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'username' => "required|username|unique:users,username,{$user->id}",
            'bidang_id' => 'nullable|exists:bidangs,id',
            'role' => 'required|in:admin,staf,pimpinan',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'nip', 'phone', 'username', 'bidang_id', 'role']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        // Hapus semua kegiatan milik user, termasuk evaluasi & realisasi
        foreach ($user->kegiatans as $kegiatan) {
            // Hapus semua evaluasi terkait
            $kegiatan->evaluasis()->delete();

            // Hapus semua realisasi dan dokumennya
            foreach ($kegiatan->realisasis as $realisasi) {
                $realisasi->dokumens()->delete();
            }
            $kegiatan->realisasis()->delete();

            // Baru hapus kegiatannya
            $kegiatan->delete();
        }

        // Terakhir, hapus user-nya
        $user->delete();

        return redirect()->route('users.index')->with(
            'success',
            'Pengguna dan seluruh data kegiatan, evaluasi, serta realisasi terkait berhasil dihapus.'
        );
    }


    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'Status user berhasil diperbarui!');
    }
}
