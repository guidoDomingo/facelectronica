<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FacturaElectronica;
use App\Services\QrCodeService;

echo "=== TESTING IMPROVED QR SERVICE ===\n\n";

echo "1. Testing system capabilities:\n";
echo "   GD Extension: " . (QrCodeService::hasGD() ? 'Available' : 'Not available') . "\n";
echo "   ImageMagick Extension: " . (QrCodeService::hasImageMagick() ? 'Available' : 'Not available') . "\n";
echo "   Available methods: " . implode(', ', QrCodeService::getAvailableMethods()) . "\n\n";

echo "2. Testing basic QR generation:\n";
try {
    $basicQR = QrCodeService::generate('Hello World Test', 'png', 200);
    if ($basicQR) {
        echo "   Basic QR: SUCCESS (" . strlen($basicQR) . " bytes)\n";
        file_put_contents('improved_basic_qr.png', $basicQR);
        echo "   Saved to improved_basic_qr.png\n";
    } else {
        echo "   Basic QR: FAILED\n";
    }
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n3. Testing SVG generation:\n";
try {
    $svgQR = QrCodeService::generate('Hello SVG Test', 'svg', 200);
    if ($svgQR) {
        echo "   SVG QR: SUCCESS (" . strlen($svgQR) . " bytes)\n";
        file_put_contents('improved_svg_qr.svg', $svgQR);
        echo "   Saved to improved_svg_qr.svg\n";
    } else {
        echo "   SVG QR: FAILED\n";
    }
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n4. Testing with actual invoice data:\n";
$factura = FacturaElectronica::whereNotNull('xml')->first();
if ($factura) {
    echo "   Found invoice CDC: {$factura->cdc}\n";
    
    // Generate the QR data like the model does
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
    echo "   QR data length: " . strlen($qrString) . " chars\n";
    
    try {
        $invoiceQR = QrCodeService::generate($qrString, 'png', 200);
        if ($invoiceQR) {
            echo "   Invoice QR: SUCCESS (" . strlen($invoiceQR) . " bytes)\n";
            file_put_contents('improved_invoice_qr.png', $invoiceQR);
            echo "   Saved to improved_invoice_qr.png\n";
        } else {
            echo "   Invoice QR: FAILED\n";
        }
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
    
    // Test the model method
    echo "\n5. Testing model QR generation:\n";
    try {
        $modelQR = $factura->generarCodigoQR();
        if ($modelQR) {
            echo "   Model QR: SUCCESS (" . strlen($modelQR) . " bytes)\n";
            file_put_contents('improved_model_qr.png', $modelQR);
            echo "   Saved to improved_model_qr.png\n";
        } else {
            echo "   Model QR: FAILED\n";
        }
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "   No invoices found\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "Check the generated files to see if QR codes look proper now.\n";
