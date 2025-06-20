<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FacturacionElectronica\FacturacionElectronicaServiceV2;
use App\Services\FacturacionElectronica\CertificadoDigitalService;
use App\Services\FacturacionElectronica\SifenValidatorService;
use App\Services\SifenClient;
use App\Services\SifenClientV2;
use App\Services\SifenClientV3;

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
        );        // Registrar SifenClient como singleton
        $this->app->singleton(SifenClient::class, function ($app) {
            return new SifenClient();
        });
        
        // Registrar SifenClientV2 como singleton (versión mejorada)
        $this->app->singleton(SifenClientV2::class, function ($app) {
            return new SifenClientV2();
        });
        
        // Registrar SifenClientV3 como singleton (Node.js API integration)
        $this->app->singleton(SifenClientV3::class, function ($app) {
            return new SifenClientV3();
        });

        // Registrar FacturacionElectronicaServiceV2 con SifenClientV3 (Node.js integration)
        $this->app->singleton(FacturacionElectronicaServiceV2::class, function ($app) {
            return new FacturacionElectronicaServiceV2(
                $app->make(SifenClientV3::class)
            );
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