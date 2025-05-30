<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FacturaElectronica;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

echo "=== Testing SVG to PNG Conversion ===\n\n";

try {
    // Generate QR as SVG first
    echo "1. Generating QR as SVG...\n";
    $svgQr = QrCode::format('svg')->size(200)->generate('Test QR Code');
    echo "SVG QR generated successfully (" . strlen($svgQr) . " bytes)\n";
    
    // Save SVG to file
    file_put_contents('test_qr.svg', $svgQr);
    echo "SVG saved to test_qr.svg\n";
    
    // Try to convert SVG to PNG using GD
    echo "\n2. Attempting SVG to PNG conversion with GD...\n";
    
    // Check if GD supports imagecreatefromstring with SVG
    if (function_exists('imagecreatefromstring')) {
        echo "imagecreatefromstring function available\n";
        
        // GD doesn't support SVG directly, let's try another approach
        // Create a simple PNG QR using basic drawing
        echo "\n3. Creating PNG QR using basic GD functions...\n";
        
        // Create a simple pattern that represents a QR code
        $size = 200;
        $moduleSize = 8; // Size of each QR module
        $modules = $size / $moduleSize;
        
        $image = imagecreate($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Create a simple pattern
        for ($x = 0; $x < $modules; $x++) {
            for ($y = 0; $y < $modules; $y++) {
                // Simple checkerboard pattern as example
                if (($x + $y) % 2 == 0) {
                    imagefilledrectangle(
                        $image, 
                        $x * $moduleSize, 
                        $y * $moduleSize, 
                        ($x + 1) * $moduleSize - 1, 
                        ($y + 1) * $moduleSize - 1, 
                        $black
                    );
                }
            }
        }
        
        // Output to PNG
        ob_start();
        imagepng($image);
        $pngData = ob_get_clean();
        imagedestroy($image);
        
        file_put_contents('test_basic_qr.png', $pngData);
        echo "Basic PNG QR created (" . strlen($pngData) . " bytes)\n";
        echo "Saved to test_basic_qr.png\n";
        
    } else {
        echo "imagecreatefromstring not available\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
