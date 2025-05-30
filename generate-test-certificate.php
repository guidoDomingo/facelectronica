<?php
/**
 * Script para generar un certificado de prueba para firma digital
 * 
 * Este script es solo para desarrollo y pruebas, NO debe usarse en producción.
 * En producción, se debe usar un certificado real emitido por una autoridad certificadora
 * reconocida por el Ministerio de Hacienda de Paraguay.
 */

// Comprobar que existe el directorio
$certPath = __DIR__ . '/storage/app/certificados';
if (!is_dir($certPath)) {
    mkdir($certPath, 0755, true);
}

// Configuración para el certificado
$config = [
    "digest_alg" => "sha256",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

// Datos para el certificado
$dn = [
    "countryName" => "PY",
    "stateOrProvinceName" => "Asuncion",
    "localityName" => "Asuncion",
    "organizationName" => "Empresa Ejemplo S.A.",
    "organizationalUnitName" => "IT",
    "commonName" => "Facturacion Electronica",
    "emailAddress" => "info@empresa.com"
];

// Generar un par de claves
echo "Generando par de claves...\n";
$privkey = openssl_pkey_new($config);
if (!$privkey) {
    echo "Error al generar las claves: " . openssl_error_string() . "\n";
    exit(1);
}

// Generar un CSR
echo "Generando solicitud de certificado...\n";
$csr = openssl_csr_new($dn, $privkey, $config);
if (!$csr) {
    echo "Error al generar la solicitud de certificado: " . openssl_error_string() . "\n";
    exit(1);
}

// Firmar el CSR para crear el certificado
echo "Firmando el certificado...\n";
$x509 = openssl_csr_sign($csr, null, $privkey, 365, $config);
if (!$x509) {
    echo "Error al firmar el certificado: " . openssl_error_string() . "\n";
    exit(1);
}

// Guardar el certificado
echo "Guardando certificado...\n";
$certFile = $certPath . '/certificado.crt';
openssl_x509_export_to_file($x509, $certFile);
echo "Certificado guardado en: {$certFile}\n";

// Guardar la clave privada
echo "Guardando clave privada...\n";
$keyFile = $certPath . '/certificado.key';
openssl_pkey_export_to_file($privkey, $keyFile);
echo "Clave privada guardada en: {$keyFile}\n";

// Crear archivo PKCS#12 (.p12)
echo "Creando archivo PKCS#12...\n";
$pkcs12File = $certPath . '/certificado.p12';
$password = 'test1234'; // Contraseña para el archivo P12

// Leer los archivos
$certData = file_get_contents($certFile);
$keyData = file_get_contents($keyFile);

// Crear el archivo PKCS#12
$result = openssl_pkcs12_export_to_file(
    $certData,
    $pkcs12File,
    $keyData,
    $password
);

if ($result) {
    echo "Archivo PKCS#12 guardado en: {$pkcs12File}\n";
    echo "Contraseña: {$password}\n";
    
    // Mostrar información para configuración
    echo "\n--- Agregar a .env ---\n";
    echo "FACTURACION_CERT_PATH={$pkcs12File}\n";
    echo "FACTURACION_CERT_CLAVE={$password}\n";
    echo "FACTURACION_FIRMA_HABILITADA=true\n";
} else {
    echo "Error al crear el archivo PKCS#12: " . openssl_error_string() . "\n";
}

echo "\nCertificado de prueba generado correctamente.\n";
echo "NOTA: Este certificado es solo para pruebas y desarrollo, NO debe usarse en producción.\n";
