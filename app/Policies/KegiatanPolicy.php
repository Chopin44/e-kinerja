<?php
// app/Policies/KegiatanPolicy.php

namespace App\Policies;

use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KegiatanPolicy
{
    use HandlesAuthorization;

    /**
     * Admin boleh semuanya lewat Gate::before (lihat AuthServiceProvider),
     * jadi di sini fokus non-admin.
     */

    // public function viewAny(User $user): bool
    // {
    //     // semua role yang valid boleh melihat daftar
    //     return $user->hasAnyRole(['admin', 'kabid', 'staf']);
    // }

    // public function view(User $user, Kegiatan $kegiatan): bool
    // {
    //     // kabid hanya di bidangnya; staf hanya kalau kegiatan di bidangnya juga
    //     if ($user->hasRole('kabid')) {
    //         return (int)$user->bidang_id === (int)$kegiatan->bidang_id;
    //     }

    //     if ($user->hasRole('staf')) {
    //         return (int)$user->bidang_id === (int)$kegiatan->bidang_id;
    //     }

    //     // admin ditangani oleh Gate::before
    //     return false;
    // }

    // public function create(User $user): bool
    // {
    //     // semua role aktif boleh create (nanti controller override bidang/user untuk non-admin)
    //     return $user->hasAnyRole(['admin', 'kabid', 'staf']);
    // }

    // public function update(User $user, Kegiatan $kegiatan): bool
    // {
    //     // staf hanya bisa update kegiatan miliknya dan di bidangnya
    //     if ($user->hasRole('staf')) {
    //         return (int)$user->bidang_id === (int)$kegiatan->bidang_id
    //             && (int)$user->id === (int)$kegiatan->user_id;
    //     }

    //     // kabid boleh update kegiatan di bidangnya (kalau kebijakanmu mau memperbolehkan)
    //     if ($user->hasRole('kabid')) {
    //         return (int)$user->bidang_id === (int)$kegiatan->bidang_id;
    //     }

    //     // admin via Gate::before
    //     return false;
    // }

    // public function delete(User $user, Kegiatan $kegiatan): bool
    // {
    //     // batasi delete ke admin saja (Gate::before)
    //     return false;
    // }
}
