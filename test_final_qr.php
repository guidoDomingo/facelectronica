<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA FINAL QR SIFEN ===\n\n";

// Test data for QR generation
$testCdc = '01-0001-011111111111111-098765432-12345-2023-1-20240312-001';
$testData = [
    'nVersion' => '150',
    'Id' => $testCdc,
    'dFeEmiDE' => '2024-03-12',
    'dRucRec' => '80090716',
    'dTotGralOpe' => '1100000',
    'dTotIVA' => '100000',
    'cItems' => '1',
    'DigestValue' => 'ABC123DEF456GHI789JKL012MNO',
    'IdCSC' => config('facturacion_electronica.id_csc', '0001')
];

echo "1. Datos de prueba:\n";
foreach ($testData as $key => $value) {
    echo "   $key = $value\n";
}

// Generate the complete SIFEN URL
$baseUrl = 'https://ekuatia.set.gov.py/consultas/qr';
$params = [];
foreach ($testData as $key => $value) {
    $params[] = $key . '=' . $value;
}
$qrString = $baseUrl . '?' . implode('&', $params);

echo "\n2. URL completa de SIFEN:\n";
echo "   $qrString\n";

echo "\n3. Análisis de la URL:\n";
echo "   Longitud: " . strlen($qrString) . " caracteres\n";
echo "   Base correcta: " . (strpos($qrString, 'https://ekuatia.set.gov.py/consultas/qr') === 0 ? 'SÍ' : 'NO') . "\n";
echo "   Parámetros: " . count($testData) . "\n";

// Test QR generation
echo "\n4. Generación de QR:\n";
try {
    $qrImage = \App\Services\QrCodeService::generate($qrString, 'png', 200);
    
    if ($qrImage && strlen($qrImage) > 100) {
        echo "   Estado: EXITOSO\n";
        echo "   Tamaño: " . strlen($qrImage) . " bytes\n";
        
        // Verify PNG header
        $isPng = (substr($qrImage, 0, 8) === "\x89PNG\r\n\x1a\n");
        echo "   Formato PNG: " . ($isPng ? "VÁLIDO" : "INVÁLIDO") . "\n";
        
        file_put_contents('qr_sifen_final.png', $qrImage);
        echo "   Archivo: qr_sifen_final.png\n";
        
        echo "\n✅ QR GENERADO EXITOSAMENTE\n";
        echo "El QR contiene la URL completa de consulta SIFEN.\n";
        echo "Puede ser escaneado para acceder directamente al sistema de verificación.\n";
        
    } else {
        echo "   Estado: FALLIDO (imagen vacía o muy pequeña)\n";
        if ($qrImage) {
            echo "   Tamaño recibido: " . strlen($qrImage) . " bytes\n";
        }
    }
    
} catch (Exception $e) {
    echo "   Estado: ERROR\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";
