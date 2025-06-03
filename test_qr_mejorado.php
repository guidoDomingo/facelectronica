<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\QrCodeService;

echo "=== TEST QR MEJORADO ===\n\n";

$data = "https://ekuatia.set.gov.py/consultas/qr?nVersion=150&Id=01800905631001001000000120231123&dFeEmiDE=2023-11-23&dRucRec=80012345-6&dTotGralOpe=1500000&dTotIVA=136364&cItems=1&DigestValue=c143e636c1d97c2be5d3e3148a23430f9034f384&IdCSC=0001";

// Generar QR
$qr = QrCodeService::generate($data, 'png', 400);

if ($qr) {
    $filename = 'qr_test_mejorado.png';
    file_put_contents($filename, $qr);
    echo "✅ QR generado exitosamente\n";
    echo "   Tamaño: " . strlen($qr) . " bytes\n";
    echo "   Archivo: {$filename}\n";

    // Verificar formato PNG
    $isPng = (substr($qr, 0, 8) === "\x89PNG\r\n\x1a\n");
    echo "   Formato PNG válido: " . ($isPng ? 'SÍ' : 'NO') . "\n";
} else {
    echo "❌ Error generando QR\n";
}

echo "\n=== TEST COMPLETO ===\n";
