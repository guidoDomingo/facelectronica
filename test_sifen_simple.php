<?php

/**
 * Script sencillo para probar la conexión SIFEN
 */

// Cargar autoloader
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\SifenClient;
use Illuminate\Support\Facades\Log;

echo "Iniciando prueba simplificada de SIFEN\n";

// Verificar si existe el certificado
$certificadoRuta = config('facturacion_electronica.firma_digital.ruta_certificado');
echo "Certificado configurado: $certificadoRuta\n";
echo "Existe: " . (file_exists($certificadoRuta) ? "Sí" : "No") . "\n";

try {
    // Crear cliente SIFEN
    $sifen = new SifenClient();
    echo "Cliente SIFEN creado exitosamente.\n";
    
    // Consulta simple para probar
    $cdc = "01800695631001001000000012023052611267896453"; // CDC de ejemplo
    echo "Consultando CDC: $cdc\n";
    
    $resultado = $sifen->consultarEstadoDocumento($cdc);
    echo "Consulta exitosa!\n";
    echo "Resultado: " . json_encode($resultado, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
