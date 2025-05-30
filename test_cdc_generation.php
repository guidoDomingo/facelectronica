<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\FacturacionElectronica\FacturacionElectronicaService;

echo "=== Test CDC Generation ===\n\n";

$service = new FacturacionElectronicaService();

// Test data similar to what might be causing the issue
$testData = [
    'tipoDocumento' => 1,
    'establecimiento' => '001',
    'punto' => '001',
    'numero' => '0000001'
];

echo "Datos de prueba:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

try {
    echo "Intentando generar CDC...\n";
    $cdc = $service->generateCDC($testData);
    echo "CDC generado: {$cdc}\n";
    echo "Longitud: " . strlen($cdc) . "\n";
    
    if (strpos($cdc, 'undefined') !== false) {
        echo "⚠️ ERROR: CDC contiene 'undefined'\n";
        echo "Posición: " . strpos($cdc, 'undefined') . "\n";
    } else {
        echo "✓ CDC parece válido\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Fin del test ===\n";
