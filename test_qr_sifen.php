<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\QrCodeService;

echo "=== Testing QR Generation with Fixed Data ===\n\n";

// Datos exactos del ejemplo de SIFEN
$testCDC = '01800695631001001000000120250603396154022058220582';
$qrUrl = "https://ekuatia.set.gov.py/consultas/qr?nVersion=150&Id=" . $testCDC;

echo "Generating QR with URL:\n";
echo $qrUrl . "\n\n";

try {
    echo "1. Generating QR code...\n";
    $qrImage = QrCodeService::generate($qrUrl, 'png', 300);
    
    if ($qrImage === null) {
        throw new Exception("QR generation returned null");
    }
    
    echo "2. Validating generated image...\n";
    if (strlen($qrImage) < 100) {
        throw new Exception("Generated image is too small: " . strlen($qrImage) . " bytes");
    }
    
    if (substr($qrImage, 0, 8) !== "\x89PNG\r\n\x1a\n") {
        throw new Exception("Output is not a valid PNG format");
    }
    
    echo "3. Saving QR code...\n";
    if (!file_put_contents('qr_sifen_test.png', $qrImage)) {
        throw new Exception("Failed to save QR code image");
    }
    
    echo "\n✅ Success!\n";
    echo "QR code generated successfully\n";
    echo "Size: " . strlen($qrImage) . " bytes\n";
    echo "File saved as: qr_sifen_test.png\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
