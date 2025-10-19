<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        // Tampilkan beserta roles (dari Spatie)
        $users = User::with(['bidang', 'roles'])->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $bidangs = Bidang::active()->get();
        // Daftar role dari Spatie
        $roles = Role::pluck('name'); // ['admin','kabid','staf', ...]
        return view('users.create', compact('bidangs', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'nip'                   => 'nullable|string|max:50',
            'username'              => 'required|string|unique:users,username',
            'bidang_id'             => 'nullable|exists:bidangs,id',
            'role'                  => 'required|in:admin,staf,kabid',
            'password'              => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name'       => $request->name,
            'nip'        => $request->nip,
            'username'   => $request->username,
            'bidang_id'  => $request->bidang_id,
            'password'   => Hash::make($request->password),
            'is_active'  => true,
        ]);

        // Tambahkan role via Spatie
        $user->assignRole($request->role);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $bidangs = Bidang::active()->get();
        $roles = Role::pluck('name');
        return view('users.edit', compact('user', 'bidangs', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'nip'                   => 'nullable|string|max:50',
            'username'              => "required|unique:users,username,{$user->id}",
            'bidang_id'             => 'nullable|exists:bidangs,id',
            'role'                  => 'required|in:admin,staf,kabid',
            'password'              => 'nullable|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'nip', 'username', 'bidang_id']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sinkronisasi role Spatie (hapus role lama, pasang baru)
        $user->syncRoles([$request->role]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        // (Opsional) cegah user menghapus dirinya sendiri
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        DB::transaction(function () use ($user) {
            // Hapus semua kegiatan milik user, termasuk evaluasi & realisasi
            foreach ($user->kegiatans as $kegiatan) {
                // Hapus evaluasi terkait (sesuaikan relasi/method jika berbeda)
                $kegiatan->evaluasis()->delete();

                // Hapus realisasi & dokumen
                foreach ($kegiatan->realisasis as $realisasi) {
                    $realisasi->dokumens()->delete();
                }
                $kegiatan->realisasis()->delete();

                // Hapus kegiatannya
                $kegiatan->delete();
            }

            // Lepas semua role
            $user->syncRoles([]);

            // Terakhir, hapus user
            $user->delete();
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna dan seluruh data terkait berhasil dihapus.');
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'Status user berhasil diperbarui!');
    }
}
