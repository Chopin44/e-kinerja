<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\Kegiatan;
use App\Models\User;
use App\Policies\KegiatanPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Kegiatan::class => KegiatanPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        // Define Gates
        Gate::define('admin', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('staf', function (User $user) {
            return $user->role === 'staf';
        });
    }
}