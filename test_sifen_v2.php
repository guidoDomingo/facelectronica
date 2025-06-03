<?php

/**
 * Script para probar el cliente SIFEN V2 mejorado
 */

// Cargar autoloader y bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\SifenClientV2;
use Illuminate\Support\Facades\Log;

echo "=== PRUEBA DE CLIENTE SIFEN V2 ===\n\n";

echo "PHP Version: " . phpversion() . "\n";
echo "SOAP Extension: " . (extension_loaded('soap') ? 'Instalada' : 'NO INSTALADA') . "\n";
echo "OpenSSL Extension: " . (extension_loaded('openssl') ? 'Instalada' : 'NO INSTALADA') . "\n";
echo "cURL Extension: " . (extension_loaded('curl') ? 'Instalada' : 'NO INSTALADA') . "\n\n";

// Verificar certificado
echo "Verificando configuración...\n";
$ambiente = config('facturacion_electronica.ambiente', 'test');
$certificadoRuta = config('facturacion_electronica.firma_digital.ruta_certificado');

echo "Ambiente: $ambiente\n";
echo "Certificado configurado: $certificadoRuta\n";

// Mostrar si existe el certificado
if (file_exists($certificadoRuta)) {
    echo "✅ Certificado encontrado en ruta configurada\n";
} else {
    echo "❌ Certificado NO encontrado en ruta configurada\n";
}

// Verificar certificado en ubicaciones alternativas
$alternativas = [
    base_path('node-service/certificado.p12'),
    storage_path('app/certificados/certificado.p12'),
    base_path('certificado.p12')
];

foreach ($alternativas as $ruta) {
    if (file_exists($ruta)) {
        echo "✅ Certificado alternativo encontrado: $ruta\n";
    }
}

echo "\nIniciando cliente SifenV2...\n";
try {
    $client = new SifenClientV2();
    echo "✅ Cliente inicializado correctamente\n\n";
    
    echo "Verificando conectividad con SIFEN...\n";
    $diagnostico = $client->verificarConectividad();
    
    echo "Ambiente: " . $diagnostico['ambiente'] . "\n";
    echo "Certificado válido: " . ($diagnostico['certificado_valido'] ? 'Sí' : 'No') . "\n";
    echo "Ruta certificado: " . $diagnostico['certificado_ruta'] . "\n\n";
    
    echo "Estado de servicios:\n";
    foreach ($diagnostico['servicios'] as $servicio => $estado) {
        echo "- $servicio: " . ($estado['soap_ok'] ? '✅ OK' : '❌ ERROR') . "\n";
        if (!$estado['soap_ok'] && isset($estado['error'])) {
            echo "  Error: " . $estado['error'] . "\n";
        }
    }
    
    // Probar consulta de estado si hay conectividad
    $todoOk = true;
    foreach ($diagnostico['servicios'] as $servicio => $estado) {
        if ($servicio === 'consulta' && !$estado['soap_ok']) {
            $todoOk = false;
        }
    }
    
    if ($todoOk) {
        echo "\nProbando consulta de estado...\n";
        
        // CDC de ejemplo para probar
        $cdcPrueba = "01800695631001001000000612022021816952191";
        
        echo "Consultando CDC: $cdcPrueba\n";
        $resultado = $client->consultarEstadoDocumento($cdcPrueba);
        
        if ($resultado['success']) {
            echo "✅ Consulta exitosa\n";
            echo "Estado: " . $resultado['estado'] . "\n";
            echo "Mensaje: " . $resultado['mensaje'] . "\n";
        } else {
            echo "❌ Error en consulta: " . $resultado['mensaje'] . "\n";
        }
    } else {
        echo "\n❌ Omitiendo prueba de consulta debido a problemas de conectividad\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
