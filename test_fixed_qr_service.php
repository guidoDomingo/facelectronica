<?php

require_once 'vendor/autoload.php';

use App\Services\QrCodeService;

// Test the improved QR service
echo "=== Testing Improved QR Service ===\n";

// Test data (Paraguay electronic invoice format)
$testData = "https://ekuatia.set.gov.py/consultas/qr?nVersion=150&Id=01800695631001001000000012024060500002&dDVId=4&cDC=01&dRUC=80069563&dDV=1&dFecEmi=20240605&dTpEmis=1&iTiDE=1";

echo "Test data length: " . strlen($testData) . "\n";
echo "Available methods: " . implode(', ', QrCodeService::getAvailableMethods()) . "\n";
echo "Has ImageMagick: " . (QrCodeService::hasImageMagick() ? 'Yes' : 'No') . "\n";
echo "Has GD: " . (QrCodeService::hasGD() ? 'Yes' : 'No') . "\n\n";

// Test PNG generation
echo "=== Testing PNG generation ===\n";
$pngQr = QrCodeService::generate($testData, 'png', 200);
if ($pngQr) {
    $filename = 'test_fixed_service.png';
    file_put_contents($filename, $pngQr);
    echo "✓ PNG QR generated successfully (" . strlen($pngQr) . " bytes)\n";
    echo "✓ Saved as: {$filename}\n";
} else {
    echo "✗ PNG QR generation failed\n";
}

// Test SVG generation
echo "\n=== Testing SVG generation ===\n";
$svgQr = QrCodeService::generate($testData, 'svg', 200);
if ($svgQr) {
    $filename = 'test_fixed_service.svg';
    file_put_contents($filename, $svgQr);
    echo "✓ SVG QR generated successfully (" . strlen($svgQr) . " bytes)\n";
    echo "✓ Saved as: {$filename}\n";
} else {
    echo "✗ SVG QR generation failed\n";
}

echo "\n=== Test completed ===\n";
