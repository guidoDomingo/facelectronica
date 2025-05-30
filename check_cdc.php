<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FacturaElectronica;

echo "=== Análisis de CDCs existentes ===\n\n";

$facturas = FacturaElectronica::take(5)->get(['id', 'cdc', 'tipo_documento', 'establecimiento', 'punto', 'numero']);

foreach($facturas as $factura) {
    echo "ID: {$factura->id}\n";
    echo "CDC: {$factura->cdc}\n";
    echo "Longitud: " . strlen($factura->cdc) . "\n";
    
    // Analizar componentes del CDC
    if (strlen($factura->cdc) >= 44) {
        $tipoDoc = substr($factura->cdc, 0, 2);
        $ruc = substr($factura->cdc, 2, 8);
        $dv = substr($factura->cdc, 10, 1);
        $establecimiento = substr($factura->cdc, 11, 3);
        $punto = substr($factura->cdc, 14, 3);
        $numero = substr($factura->cdc, 17, 7);
        $fecha = substr($factura->cdc, 24, 8);
        $random = substr($factura->cdc, 32, 8);
        $codigoSeguridad = substr($factura->cdc, 40, 4);
        
        echo "  Tipo Doc: {$tipoDoc}\n";
        echo "  RUC: {$ruc}\n";
        echo "  DV: {$dv}\n";
        echo "  Establecimiento: {$establecimiento}\n";
        echo "  Punto: {$punto}\n";
        echo "  Número: {$numero}\n";
        echo "  Fecha: {$fecha}\n";
        echo "  Random: {$random}\n";
        echo "  Código Seg: {$codigoSeguridad}\n";
    }
    
    // Buscar "undefined"
    if (strpos($factura->cdc, 'undefined') !== false) {
        echo "  ⚠️ CONTIENE 'undefined' en posición: " . strpos($factura->cdc, 'undefined') . "\n";
    }
    
    echo "\n";
}

echo "=== Fin del análisis ===\n";
