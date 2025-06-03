<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Services\QrCodeService;

echo "=== Testing SIFEN QR Generation with Simple URL ===\n\n";

// Test with the exact CDC from your screenshot
$cdc = '01800695631001001000000120250603396154022058220582';
$url = "https://ekuatia.set.gov.py/consultas/qr?nVersion=150&Id={$cdc}";

echo "1. Testing with URL:\n";
echo $url . "\n\n";

try {
    echo "2. Generating QR code...\n";
    $qrImage = QrCodeService::generate($url, 'png', 300);
    
    if ($qrImage === null) {
        throw new Exception("QR generation returned null");
    }
    
    echo "3. Validating generated QR...\n";
    $size = strlen($qrImage);
    echo "   Size: {$size} bytes\n";
    
    if (substr($qrImage, 0, 8) === "\x89PNG\r\n\x1a\n") {
        echo "   Format: Valid PNG\n";
    } else {
        echo "   WARNING: Not a valid PNG format\n";
        echo "   First bytes: " . bin2hex(substr($qrImage, 0, 8)) . "\n";
    }
    
    echo "\n4. Saving QR code...\n";
    $filename = 'qr_sifen_debug.png';
    if (file_put_contents($filename, $qrImage)) {
        echo "   Saved to: {$filename}\n";
    } else {
        echo "   ERROR: Could not save file\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
