<?php
/**
 * Script para probar la conexión a SIFEN directamente
 */

// Cargamos el autoloader de Composer
require __DIR__ . '/vendor/autoload.php';

// Cargamos el bootstrap de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\SifenClient;
use Illuminate\Support\Facades\Log;

echo "=== Diagnóstico de conexión a SIFEN ===\n\n";

// Información del sistema
echo "PHP Version: " . phpversion() . "\n";
echo "SOAP Extension: " . (extension_loaded('soap') ? 'Instalada' : 'NO INSTALADA') . "\n";
echo "OpenSSL Extension: " . (extension_loaded('openssl') ? 'Instalada' : 'NO INSTALADA') . "\n";
echo "cURL Extension: " . (extension_loaded('curl') ? 'Instalada' : 'NO INSTALADA') . "\n\n";

// Verificar certificado
$ambiente = config('facturacion_electronica.ambiente', 'test');
$certificadoRuta = config('facturacion_electronica.firma_digital.ruta_certificado');
$certificadoClave = config('facturacion_electronica.firma_digital.clave_certificado');

echo "Ambiente: $ambiente\n";
echo "Certificado: $certificadoRuta\n\n";

// Verificar si el certificado existe
echo "Verificando certificado...\n";
if (file_exists($certificadoRuta)) {
    echo "✅ Certificado encontrado\n";
    if (is_readable($certificadoRuta)) {
        echo "✅ Certificado legible\n";
    } else {
        echo "❌ Certificado no legible - Revisar permisos\n";
    }
} else {
    echo "❌ Certificado no encontrado - Verificar ruta\n";
}
echo "\n";

// Test directo de conectividad usando cURL
echo "Probando conectividad básica...\n";
$url = $ambiente == 'test' ? 
    'https://sifen-test.set.gov.py/de/ws/sync-services.wsdl' : 
    'https://sifen.set.gov.py/de/ws/sync-services.wsdl';

try {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    
    if ($error) {
        echo "❌ Error de conexión: $error\n";
    } else {
        echo "✅ Respuesta HTTP: " . $info['http_code'] . "\n";
        echo "✅ Tamaño de respuesta: " . $info['size_download'] . " bytes\n";
        
        if (strpos($response, 'wsdl:definitions') !== false || strpos($response, '<definitions') !== false) {
            echo "✅ Contenido WSDL detectado\n";
        } else {
            echo "❌ Contenido no parece ser un WSDL válido\n";
            echo "Primeros 100 caracteres: " . substr($response, 0, 100) . "...\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Excepción al conectar: " . $e->getMessage() . "\n";
}
echo "\n";

// Iniciar prueba usando la clase SifenClient
echo "Iniciando prueba con SifenClient...\n";
try {
    $client = new SifenClient();
    $diagnostico = $client->testConexion();
    
    echo "=== RESULTADOS DE DIAGNÓSTICO ===\n";
    
    // Mostrar resultados del certificado
    echo "\nCERTIFICADO:\n";
    echo "Existe: " . ($diagnostico['certificado']['existe'] ? 'Sí' : 'No') . "\n";
    echo "Accesible: " . ($diagnostico['certificado']['accesible'] ? 'Sí' : 'No') . "\n";
    echo "Mensaje: " . $diagnostico['certificado']['mensaje'] . "\n";
    
    // Mostrar resultados de conectividad
    echo "\nCONECTIVIDAD:\n";
    echo "OK: " . ($diagnostico['conectividad']['ok'] ? 'Sí' : 'No') . "\n";
    echo "Mensaje: " . $diagnostico['conectividad']['mensaje'] . "\n";
    if (!empty($diagnostico['conectividad']['headers'])) {
        echo "Headers:\n";
        foreach ($diagnostico['conectividad']['headers'] as $header) {
            echo "  - $header\n";
        }
    }
    
    // Mostrar resultados de SOAP
    echo "\nSOAP:\n";
    echo "OK: " . ($diagnostico['soap']['ok'] ? 'Sí' : 'No') . "\n";
    echo "Mensaje: " . $diagnostico['soap']['mensaje'] . "\n";
    if (!empty($diagnostico['soap']['functions'])) {
        echo "Funciones disponibles:\n";
        foreach ($diagnostico['soap']['functions'] as $function) {
            echo "  - $function\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error al ejecutar prueba: " . $e->getMessage() . "\n";
    echo "Traza: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Fin del diagnóstico ===\n";
