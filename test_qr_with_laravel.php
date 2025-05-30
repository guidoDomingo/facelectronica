<?php

// Bootstrap Laravel for testing outside web context
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\QrCodeService;

echo "=== Testing Fixed QR Service (with Laravel bootstrap) ===\n";

// Test data (Paraguay electronic invoice format)
$testData = "https://ekuatia.set.gov.py/consultas/qr?nVersion=150&Id=01800695631001001000000012024060500002&dDVId=4&cDC=01&dRUC=80069563&dDV=1&dFecEmi=20240605&dTpEmis=1&iTiDE=1";

echo "Test data length: " . strlen($testData) . "\n";
echo "Available methods: " . implode(', ', QrCodeService::getAvailableMethods()) . "\n";
echo "Has ImageMagick: " . (QrCodeService::hasImageMagick() ? 'Yes' : 'No') . "\n";
echo "Has GD: " . (QrCodeService::hasGD() ? 'Yes' : 'No') . "\n\n";

// Test PNG generation
echo "=== Testing PNG generation ===\n";
$pngQr = QrCodeService::generate($testData, 'png', 200);
if ($pngQr && strlen($pngQr) > 100) {
    $filename = 'test_fixed_service.png';
    file_put_contents($filename, $pngQr);
    echo "✓ PNG QR generated successfully (" . strlen($pngQr) . " bytes)\n";
    echo "✓ Saved as: {$filename}\n";
    
    // Check if it's a proper PNG (should start with PNG header)
    $header = substr($pngQr, 0, 8);
    $pngHeader = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
    if ($header === $pngHeader) {
        echo "✓ Valid PNG header detected\n";
    } else {
        echo "! Warning: PNG header not detected (may be fallback pattern)\n";
    }
} else {
    echo "✗ PNG QR generation failed\n";
}

// Test SVG generation
echo "\n=== Testing SVG generation ===\n";
$svgQr = QrCodeService::generate($testData, 'svg', 200);
if ($svgQr && strlen($svgQr) > 100) {
    $filename = 'test_fixed_service.svg';
    file_put_contents($filename, $svgQr);
    echo "✓ SVG QR generated successfully (" . strlen($svgQr) . " bytes)\n";
    echo "✓ Saved as: {$filename}\n";
    
    // Check if it contains QR patterns
    if (strpos($svgQr, '<svg') !== false && strpos($svgQr, '<rect') !== false) {
        echo "✓ Valid SVG structure detected\n";
    }
} else {
    echo "✗ SVG QR generation failed\n";
}

echo "\n=== Test completed ===\n";
