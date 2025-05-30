<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Simple QR Test ===\n\n";

try {
    echo "Testing QR Code generation with simple text...\n";
    
    // Test 1: Try the facade directly
    $qr = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
        ->size(200)
        ->generate('Hello World');
        
    if ($qr) {
        echo "SUCCESS: QR generated with " . strlen($qr) . " bytes\n";
        
        // Save to file for verification
        file_put_contents('test_qr.png', $qr);
        echo "QR saved to test_qr.png\n";
    } else {
        echo "FAILED: No QR data returned\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
