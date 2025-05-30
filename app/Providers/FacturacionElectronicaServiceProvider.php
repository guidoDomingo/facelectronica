<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FacturacionElectronica\FacturacionElectronicaService;
use App\Services\FacturacionElectronica\CertificadoDigitalService;
use App\Services\FacturacionElectronica\SifenValidatorService;

class FacturacionElectronicaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar el archivo de configuración
        $this->mergeConfigFrom(
            __DIR__.'/../../config/facturacion_electronica.php', 'facturacion_electronica'
        );
          // Registrar los servicios como singleton
        $this->app->singleton(FacturacionElectronicaService::class, function ($app) {
            return new FacturacionElectronicaService();
        });
        
        $this->app->singleton(CertificadoDigitalService::class, function ($app) {
            return new CertificadoDigitalService();
        });
        
        $this->app->singleton(SifenValidatorService::class, function ($app) {
            return new SifenValidatorService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publicar el archivo de configuración
        $this->publishes([
            __DIR__.'/../../config/facturacion_electronica.php' => config_path('facturacion_electronica.php'),
        ], 'facturacion-electronica-config');
    }
}