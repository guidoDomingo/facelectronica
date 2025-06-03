<?php

require __DIR__.'/vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;

// Test data
$data = "https://ekuatia.set.gov.py/consultas/qr?nVersion=150&Id=018006956310010010000001202506033961";
$size = 300;

try {
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "GD Installed: " . (extension_loaded('gd') ? 'Yes' : 'No') . "\n\n";
    
    echo "Creating QR code...\n";
    $qr = QrCode::create($data)
        ->setSize($size)
        ->setMargin(1)
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->setForegroundColor(new Color(0, 0, 0))
        ->setBackgroundColor(new Color(255, 255, 255))
        ->setEncoding(new Encoding('UTF-8'));

    echo "Writing QR code to PNG...\n";
    $result = (new PngWriter())->write($qr);
    
    $pngData = $result->getString();
    
    echo "Saving to file...\n";
    file_put_contents('debug_qr.png', $pngData);
    
    echo "Validating output...\n";
    if (strlen($pngData) < 100) {
        throw new Exception("Generated PNG is too small: " . strlen($pngData) . " bytes");
    }
    
    if (substr($pngData, 0, 8) !== "\x89PNG\r\n\x1a\n") {
        throw new Exception("Invalid PNG header");
    }
    
    $image = @imagecreatefromstring($pngData);
    if ($image === false) {
        throw new Exception("Failed to load generated image with GD");
    }
    imagedestroy($image);
    
    echo "\n✅ Success! QR code generated and validated.\n";
    echo "Output saved to debug_qr.png\n";
    echo "File size: " . strlen($pngData) . " bytes\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
