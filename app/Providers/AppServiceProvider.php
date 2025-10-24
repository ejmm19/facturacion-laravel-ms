<?php

namespace App\Providers;

use App\Models\Factura;
use App\Observers\FacturaObserver;
use App\Services\ClienteService;
use App\Services\ProductoService;
use App\Services\FacturaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registro de servicios como singletons
        $this->app->singleton(ClienteService::class);
        $this->app->singleton(ProductoService::class);
        $this->app->singleton(FacturaService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar el observer de facturas
        Factura::observe(FacturaObserver::class);
    }
}
