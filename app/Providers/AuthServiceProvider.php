<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\Kegiatan;
use App\Policies\KegiatanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan policy eksplisit (opsional, auto-discovery biasanya cukup).
     */
    protected $policies = [
        Kegiatan::class => KegiatanPolicy::class,
    ];

    public function boot(): void
    {
        // Admin = superuser. Jika user punya role 'admin' (Spatie), lolos semua ability.
        Gate::before(function ($user, string $ability) {
            return method_exists($user, 'hasRole') && $user->hasRole('admin') ? true : null;
        });

        // Gates helper (opsional untuk @can('staf') / middleware can:staf)
        Gate::define('staf', fn ($user) => method_exists($user, 'hasRole') && $user->hasRole('staf'));
        Gate::define('pimpinan', fn ($user) => method_exists($user, 'hasRole') && $user->hasRole('pimpinan'));
    }
}
