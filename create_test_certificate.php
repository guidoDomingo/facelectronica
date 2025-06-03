<?php

/**
 * Script para generar un certificado de prueba para SIFEN
 * 
 * Este script crea un certificado p12 de prueba para usar con la integración de SIFEN
 * Nota: Este certificado NO es válido para producción, solo para pruebas
 */

// Obtener ruta de storage desde Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Generando certificado de prueba para SIFEN ===\n\n";

// Obtener la ruta configurada para el certificado
$certificadoRuta = config('facturacion_electronica.firma_digital.ruta_certificado');
$certificadoClave = config('facturacion_electronica.firma_digital.clave_certificado');

echo "Ruta configurada: $certificadoRuta\n";
echo "Clave configurada: $certificadoClave\n\n";

// Verificar que el directorio exista
$certDir = dirname($certificadoRuta);
if (!file_exists($certDir)) {
    echo "Creando directorio para certificados: $certDir\n";
    if (!mkdir($certDir, 0755, true)) {
        echo "ERROR: No se pudo crear el directorio\n";
        exit(1);
    }
}

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

echo "✅ Certificado de prueba generado exitosamente\n";
echo "   Ubicación: $certificadoRuta\n";
echo "   Clave: $certificadoClave\n\n";
echo "IMPORTANTE: Este certificado es SOLO PARA PRUEBAS y NO es válido para producción.\n";
echo "           Para producción, debe obtener un certificado válido de una entidad certificadora autorizada.\n";
