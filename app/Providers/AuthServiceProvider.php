<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        // Solo admin / config.ver puede ver logs (última oportunidad: errores rastreables).
        Gate::define('viewLogViewer', function ($user = null) {
            if (! $user) return false;
            return $user->hasRole('admin') || $user->can('config.ver');
        });
    }
}
