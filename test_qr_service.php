<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\QrCodeService;
use App\Models\FacturaElectronica;

echo "=== Testing QrCodeService ===\n\n";

echo "1. Checking available methods...\n";
$methods = QrCodeService::getAvailableMethods();
echo "Available methods: " . implode(', ', $methods) . "\n";
echo "ImageMagick available: " . (QrCodeService::hasImageMagick() ? 'Yes' : 'No') . "\n";
echo "GD available: " . (QrCodeService::hasGD() ? 'Yes' : 'No') . "\n\n";

echo "2. Testing basic QR generation with our service...\n";
try {
    $basicQR = QrCodeService::generate('Hello World', 'png', 200);
    if ($basicQR) {
        echo "SUCCESS: Basic QR generated (" . strlen($basicQR) . " bytes)\n";
        file_put_contents('test_service_basic.png', $basicQR);
        echo "Saved to test_service_basic.png\n";
    } else {
        echo "FAILED: Basic QR returned null\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n3. Testing SVG generation...\n";
try {
    $svgQR = QrCodeService::generate('Hello World SVG', 'svg', 200);
    if ($svgQR) {
        echo "SUCCESS: SVG QR generated (" . strlen($svgQR) . " bytes)\n";
        file_put_contents('test_service_svg.svg', $svgQR);
        echo "Saved to test_service_svg.svg\n";
    } else {
        echo "FAILED: SVG QR returned null\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n4. Testing with invoice data...\n";
$invoice = FacturaElectronica::whereNotNull('xml')->first();
if ($invoice) {
    echo "Found invoice ID: {$invoice->id}, CDC: {$invoice->cdc}\n";
    
    try {
        $qrData = [
            'nVersion=150',
            'Id=' . $invoice->cdc,
            'dFeEmiDE=' . $invoice->fecha->format('Y-m-d'),
            'dRucRec=' . $invoice->ruc_receptor,
            'dTotGralOpe=' . number_format($invoice->total, 0, '', ''),
            'dTotIVA=' . number_format($invoice->impuesto, 0, '', ''),
            'cItems=1',
            'DigestValue=' . substr(hash('sha256', $invoice->xml ?? $invoice->cdc), 0, 28),
            'IdCSC=' . config('facturacion_electronica.id_csc', '0001')
        ];
        
        $qrString = implode('&', $qrData);
        echo "QR String: $qrString\n";
        
        $invoiceQR = QrCodeService::generate($qrString, 'png', 200);
        if ($invoiceQR) {
            echo "SUCCESS: Invoice QR generated (" . strlen($invoiceQR) . " bytes)\n";
            file_put_contents('test_service_invoice.png', $invoiceQR);
            echo "Saved to test_service_invoice.png\n";
        } else {
            echo "FAILED: Invoice QR returned null\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "No invoices with XML found\n";
}

echo "\n=== Test Complete ===\n";
