<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Services\QrCodeService;

// Test specific CDC from screenshot
$cdc = '01800695631001001000000120250603396154022058220582';
$url = "https://ekuatia.set.gov.py/consultas/qr?nVersion=150&Id=" . $cdc;
$size = 300;

// Try both PNG and SVG formats
foreach (['png', 'svg'] as $format) {
    try {
        echo "\nTesting {$format} generation:\n";
        echo "URL: {$url}\n";
        
        $qrData = QrCodeService::generate($url, $format, $size);
        
        if ($qrData === null) {
            echo "❌ QR generation failed (null returned)\n";
            continue;
        }
        
        $filename = "test_qr." . $format;
        file_put_contents($filename, $qrData);
        
        echo "✅ QR generated successfully:\n";
        echo "   Size: " . strlen($qrData) . " bytes\n";
        echo "   Saved as: {$filename}\n";
        
        if ($format === 'png') {
            $isPng = (substr($qrData, 0, 8) === "\x89PNG\r\n\x1a\n");
            echo "   Valid PNG: " . ($isPng ? "YES" : "NO") . "\n";
            
            if ($gd = @imagecreatefromstring($qrData)) {
                echo "   Image size: " . imagesx($gd) . "x" . imagesy($gd) . "\n";
                imagedestroy($gd);
            } else {
                echo "   ⚠️ Warning: Generated file is not a valid image\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error testing {$format}: " . $e->getMessage() . "\n";
    }
}
