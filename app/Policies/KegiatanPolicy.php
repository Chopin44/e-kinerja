<?php
// app/Policies/KegiatanPolicy.php

namespace App\Policies;

use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KegiatanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Kegiatan $kegiatan)
    {
        return $user->role === 'admin' || $user->bidang_id === $kegiatan->bidang_id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Kegiatan $kegiatan)
    {
        return $user->role === 'admin' || 
               ($user->bidang_id === $kegiatan->bidang_id && $user->id === $kegiatan->user_id);
    }

    public function delete(User $user, Kegiatan $kegiatan)
    {
        return $user->role === 'admin';
    }
}