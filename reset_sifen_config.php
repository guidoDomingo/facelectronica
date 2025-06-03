<?php
/**
 * Script para resetear la configuración de SIFEN
 * 
 * Este script limpia los certificados y regenera uno de prueba
 * Es útil cuando hay problemas de conexión relacionados con certificados
 */

// Cargar autoloader y bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Log;

echo "=== RESETEO DE CONFIGURACIÓN SIFEN ===\n\n";

// Obtener la ruta configurada para el certificado
$certificadoRuta = config('facturacion_electronica.firma_digital.ruta_certificado');
$certificadoClave = config('facturacion_electronica.firma_digital.clave_certificado');

echo "Ruta configurada: $certificadoRuta\n";
echo "Clave configurada: $certificadoClave\n\n";

// Eliminar el certificado si existe
if (file_exists($certificadoRuta)) {
    echo "Eliminando certificado existente...\n";
    unlink($certificadoRuta);
}

// Verificar que el directorio exista
$certDir = dirname($certificadoRuta);
if (!file_exists($certDir)) {
    echo "Creando directorio para certificados: $certDir\n";
    if (!mkdir($certDir, 0755, true)) {
        echo "ERROR: No se pudo crear el directorio\n";
        exit(1);
    }
}

// Si hay un certificado en node-service, copiarlo
$nodeCertPath = base_path('node-service/certificado.p12');
if (file_exists($nodeCertPath)) {
    echo "Copiando certificado desde node-service...\n";
    if (!copy($nodeCertPath, $certificadoRuta)) {
        echo "ERROR: No se pudo copiar el certificado\n";
    } else {
        echo "Certificado copiado correctamente\n";
    }
} else {
    // Crear un nuevo certificado de prueba
    echo "No hay certificado en node-service, generando uno nuevo...\n";
    
    // Crear un nuevo par de claves
    echo "Generando par de claves...\n";
    $privateKey = openssl_pkey_new([
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);

    if (!$privateKey) {
        echo "ERROR: No se pudo generar la clave privada: " . openssl_error_string() . "\n";
        exit(1);
    }

    // Crear certificado X.509
    echo "Creando certificado X.509 autofirmado...\n";
    $dn = [
        "countryName" => "PY",
        "stateOrProvinceName" => "Paraguay",
        "localityName" => "Asunción",
        "organizationName" => "Empresa Demo SIFEN",
        "organizationalUnitName" => "TI",
        "commonName" => "Certificado de Prueba",
        "emailAddress" => "test@example.com"
    ];

    $csr = openssl_csr_new($dn, $privateKey);
    $x509 = openssl_csr_sign($csr, null, $privateKey, 365);

    // Exportar a archivo PEM
    echo "Exportando certificado y clave privada...\n";
    openssl_x509_export($x509, $certPem);
    openssl_pkey_export($privateKey, $keyPem);

    // Crear archivo PKCS#12 (.p12)
    echo "Creando archivo PKCS#12 (.p12)...\n";
    $pkcs12 = [];
    if (!openssl_pkcs12_export($x509, $pkcs12Export, $keyPem, $certificadoClave)) {
        echo "ERROR: No se pudo exportar PKCS#12: " . openssl_error_string() . "\n";
        exit(1);
    }

    // Guardar archivo .p12
    if (file_put_contents($certificadoRuta, $pkcs12Export) === false) {
        echo "ERROR: No se pudo guardar el archivo en $certificadoRuta\n";
        exit(1);
    }
    
    echo "Certificado de prueba generado correctamente\n";
}

// Verificar que el certificado exista
if (file_exists($certificadoRuta)) {
    echo "\n✅ Certificado listo en: $certificadoRuta\n";
} else {
    echo "\n❌ ERROR: No se pudo generar el certificado\n";
    exit(1);
}

// Limpiar cache de WSDL
$wsdlCachePath = sys_get_temp_dir() . '/wsdl-*';
$files = glob($wsdlCachePath);

if ($files) {
    echo "\nLimpiando cache de WSDL...\n";
    foreach ($files as $file) {
        unlink($file);
    }
    echo count($files) . " archivos de cache eliminados\n";
} else {
    echo "\nNo se encontró cache de WSDL para limpiar\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";
echo "Ahora puede probar la conexión con SIFEN ejecutando:\n";
echo "php test_sifen_v2.php\n";
