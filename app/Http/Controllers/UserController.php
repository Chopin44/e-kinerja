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
    /**
     * Tampilkan daftar user.
     */
    public function index()
    {
        $users = User::with(['bidang', 'roles'])->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Form tambah user baru.
     */
    public function create()
    {
        $bidangs = Bidang::active()->get();
        $roles = Role::pluck('name'); // contoh: ['admin','kabid','staf']
        return view('users.create', compact('bidangs', 'roles'));
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'nip'        => 'nullable|string|max:50',
            'username'   => 'required|string|unique:users,username',
            'bidang_id'  => 'nullable|exists:bidangs,id',
            'role'       => 'required|in:admin,staf,kabid',
            'password'   => 'required|min:6|confirmed',
        ]);

        // Jika role admin atau kabid, kosongkan bidang_id
        $bidangId = in_array($request->role, ['admin'])
            ? null
            : $request->bidang_id;

        $user = User::create([
            'name'       => $request->name,
            'nip'        => $request->nip,
            'username'   => $request->username,
            'bidang_id'  => $bidangId,
            'password'   => Hash::make($request->password),
            'is_active'  => true,
        ]);

        // Tambahkan role ke user (Spatie)
        $user->assignRole($request->role);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Form edit user.
     */
    public function edit(User $user)
    {
        $bidangs = Bidang::active()->get();
        $roles = Role::pluck('name');
        return view('users.edit', compact('user', 'bidangs', 'roles'));
    }

    /**
     * Update data user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'nip'        => 'nullable|string|max:50',
            'username'   => "required|unique:users,username,{$user->id}",
            'bidang_id'  => 'nullable|exists:bidangs,id',
            'role'       => 'required|in:admin,staf,kabid',
            'password'   => 'nullable|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'nip', 'username']);

        // Jika admin atau kabid, kosongkan bidang_id
        if (in_array($request->role, ['admin'])) {
            $data['bidang_id'] = null;
        } else {
            $data['bidang_id'] = $request->bidang_id;
        }

        // Jika password diisi, enkripsi dan update
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sinkronisasi role Spatie (hapus lama, pasang baru)
        $user->syncRoles([$request->role]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui!');
    }

    /**
     * Hapus user dan semua data terkait.
     */
    public function destroy(User $user)
    {
        // Opsional: cegah user menghapus dirinya sendiri
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        DB::transaction(function () use ($user) {
            // Hapus semua kegiatan milik user, termasuk evaluasi & realisasi
            foreach ($user->kegiatans as $kegiatan) {
                // Hapus evaluasi terkait
                $kegiatan->evaluasis()->delete();

                // Hapus realisasi & dokumen
                foreach ($kegiatan->realisasis as $realisasi) {
                    $realisasi->dokumens()->delete();
                }
                $kegiatan->realisasis()->delete();

                // Hapus kegiatan
                $kegiatan->delete();
            }

            // Lepas semua role
            $user->syncRoles([]);

            // Hapus user
            $user->delete();
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna dan seluruh data terkait berhasil dihapus.');
    }

    /**
     * Aktif/nonaktifkan user.
     */
    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'Status user berhasil diperbarui!');
    }
}
