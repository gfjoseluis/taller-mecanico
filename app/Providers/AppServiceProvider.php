<?php

namespace App\Providers;

use App\Domain\Contracts\FacturacionGatewayInterface;
use App\Infrastructure\Siat\HttpSiatGateway;
use App\Infrastructure\Siat\MockSiatGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FacturacionGatewayInterface::class, function () {
            return config('siat.driver') === 'http'
                ? new HttpSiatGateway
                : new MockSiatGateway;
        });
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.admin', function ($v) {
            $v->with('avisos', app(\App\Application\Services\NotificacionService::class)->avisos());
        });
    }
}
