<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Most basic QR test possible
require_once __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

try {
    $data = "Test QR Code";
    echo "Creating QR Code...\n";
    
    $qr = QrCode::create($data)
        ->setSize(300);
    
    echo "Creating writer...\n";
    $writer = new PngWriter();
    
    echo "Writing QR code...\n";
    $result = $writer->write($qr);
    
    echo "Getting string output...\n";
    $pngData = $result->getString();
    
    echo "Generated " . strlen($pngData) . " bytes of PNG data\n";
    echo "First 10 bytes: " . bin2hex(substr($pngData, 0, 10)) . "\n";
    
    echo "Attempting to save...\n";
    if (@file_put_contents('basic_test.png', $pngData)) {
        echo "File saved successfully!\n";
    } else {
        echo "Failed to save file\n";
        echo "Last PHP error: " . error_get_last()['message'] . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
