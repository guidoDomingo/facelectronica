<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FacturaElectronica;
use App\Services\QrCodeService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

echo "=== DIAGNOSING QR GENERATION ISSUE ===\n\n";

// 1. Test basic QR generation with different data
echo "1. Testing basic QR generation:\n";

// Test with simple text
try {
    $simpleQR = QrCode::format('png')->size(200)->generate('Hello World');
    echo "   Simple text QR: " . (strlen($simpleQR)) . " bytes\n";
    file_put_contents('simple_test.png', $simpleQR);
    echo "   Saved to simple_test.png\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

// Test with complex data
try {
    $complexData = 'nVersion=150&Id=01800695631001001000000120250529552490131773&dFeEmiDE=2025-05-29&dRucRec=5004615-5&dTotGralOpe=35000&dTotIVA=3500&cItems=2&DigestValue=7f0bd49e6c6fd07ba57bc015bb27&IdCSC=0001';
    $complexQR = QrCode::format('png')->size(200)->generate($complexData);
    echo "   Complex data QR: " . (strlen($complexQR)) . " bytes\n";
    file_put_contents('complex_test.png', $complexQR);
    echo "   Saved to complex_test.png\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n2. Testing with different QR settings:\n";

// Test with different error correction levels
try {
    $qrLow = QrCode::format('png')->size(200)->errorCorrection('L')->generate('Test QR Low');
    echo "   Low error correction: " . strlen($qrLow) . " bytes\n";
    file_put_contents('qr_low.png', $qrLow);
    
    $qrMedium = QrCode::format('png')->size(200)->errorCorrection('M')->generate('Test QR Medium');
    echo "   Medium error correction: " . strlen($qrMedium) . " bytes\n";
    file_put_contents('qr_medium.png', $qrMedium);
    
    $qrHigh = QrCode::format('png')->size(200)->errorCorrection('H')->generate('Test QR High');
    echo "   High error correction: " . strlen($qrHigh) . " bytes\n";
    file_put_contents('qr_high.png', $qrHigh);
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n3. Testing with different sizes:\n";

try {
    $qr100 = QrCode::format('png')->size(100)->generate('Size Test 100');
    echo "   Size 100: " . strlen($qr100) . " bytes\n";
    file_put_contents('qr_size_100.png', $qr100);
    
    $qr200 = QrCode::format('png')->size(200)->generate('Size Test 200');
    echo "   Size 200: " . strlen($qr200) . " bytes\n";
    file_put_contents('qr_size_200.png', $qr200);
    
    $qr300 = QrCode::format('png')->size(300)->generate('Size Test 300');
    echo "   Size 300: " . strlen($qr300) . " bytes\n";
    file_put_contents('qr_size_300.png', $qr300);
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n4. Testing with actual invoice data:\n";

$factura = FacturaElectronica::whereNotNull('xml')->first();
if ($factura) {
    echo "   Found invoice CDC: {$factura->cdc}\n";
    
    // Build QR data exactly like in the model
    $digestValue = '';
    if (preg_match('/<DigestValue>(.*?)<\/DigestValue>/', $factura->xml, $matches)) {
        $digestValue = $matches[1];
    } else {
        $digestValue = substr(hash('sha256', $factura->xml), 0, 28);
    }
    
    $itemCount = 1;
    if (strpos($factura->xml, '<gCamItem>') !== false) {
        $itemCount = substr_count($factura->xml, '<gCamItem>');
    }
    
    $qrData = [
        'nVersion=150',
        'Id=' . $factura->cdc,
        'dFeEmiDE=' . $factura->fecha->format('Y-m-d'),
        'dRucRec=' . $factura->ruc_receptor,
        'dTotGralOpe=' . number_format($factura->total, 0, '', ''),
        'dTotIVA=' . number_format($factura->impuesto, 0, '', ''),
        'cItems=' . $itemCount,
        'DigestValue=' . $digestValue,
        'IdCSC=' . config('facturacion_electronica.id_csc', '0001')
    ];
    
    $qrString = implode('&', $qrData);
    echo "   QR String length: " . strlen($qrString) . " chars\n";
    echo "   QR String: " . substr($qrString, 0, 100) . "...\n";
    
    try {
        // Test with default settings
        $invoiceQR1 = QrCode::format('png')->size(200)->generate($qrString);
        echo "   Invoice QR (default): " . strlen($invoiceQR1) . " bytes\n";
        file_put_contents('invoice_qr_default.png', $invoiceQR1);
        
        // Test with high error correction and margin
        $invoiceQR2 = QrCode::format('png')
                            ->size(200)
                            ->errorCorrection('H')
                            ->margin(2)
                            ->generate($qrString);
        echo "   Invoice QR (high+margin): " . strlen($invoiceQR2) . " bytes\n";
        file_put_contents('invoice_qr_enhanced.png', $invoiceQR2);
        
        // Test with SVG format
        $invoiceQRSvg = QrCode::format('svg')->size(200)->generate($qrString);
        echo "   Invoice QR (SVG): " . strlen($invoiceQRSvg) . " bytes\n";
        file_put_contents('invoice_qr.svg', $invoiceQRSvg);
        
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n5. Checking QR library configuration:\n";

// Check what QR library is actually being used
$reflection = new ReflectionClass(QrCode::class);
echo "   QR Facade class: " . $reflection->getName() . "\n";

// Check available methods
echo "   Available methods: " . implode(', ', get_class_methods(QrCode::class)) . "\n";

echo "\n=== DIAGNOSIS COMPLETE ===\n";
echo "Check the generated PNG files to see the differences.\n";
echo "Files created:\n";
echo "- simple_test.png\n";
echo "- complex_test.png\n";
echo "- qr_low.png, qr_medium.png, qr_high.png\n";
echo "- qr_size_100.png, qr_size_200.png, qr_size_300.png\n";
echo "- invoice_qr_default.png, invoice_qr_enhanced.png\n";
echo "- invoice_qr.svg\n";
