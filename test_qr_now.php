<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\QrCodeService;
use Illuminate\Support\Facades\Log;

// Verificar GD
echo "Checking GD extension...\n";
if (!extension_loaded('gd')) {
    echo "❌ GD extension not installed!\n";
    exit(1);
}
echo "✅ GD extension loaded\n\n";

// Test data
$data = "https://ekuatia.set.gov.py/consultas/qr?nVersion=150&Id=018006956310010010000001202506033961";

try {
    echo "Generating QR code...\n";
    $qr = QrCodeService::generate($data, 'png', 300);

    if ($qr === null) {
        echo "❌ QR generation failed (null returned)\n";
        exit(1);
    }

    file_put_contents('test_qr_now.png', $qr);
    echo "✅ QR code generated successfully\n";
    echo "   Size: " . strlen($qr) . " bytes\n";
    
    // Verify PNG format
    $isPng = (substr($qr, 0, 8) === "\x89PNG\r\n\x1a\n");
    echo "   Valid PNG format: " . ($isPng ? "YES" : "NO") . "\n";
    
    // Check if file is readable
    $gd = @imagecreatefrompng('test_qr_now.png');
    if ($gd === false) {
        echo "❌ Generated file is not a valid PNG\n";
    } else {
        imagedestroy($gd);
        echo "✅ Generated file is a valid PNG image\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
