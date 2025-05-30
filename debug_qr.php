<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FacturaElectronica;

echo "=== Debugging QR Generation ===\n\n";

// Get a specific invoice
$invoice = FacturaElectronica::where('xml', '!=', null)->first();

if ($invoice) {
    echo "Found invoice ID: {$invoice->id}\n";
    echo "CDC: {$invoice->cdc}\n";
    echo "Has XML: " . (!empty($invoice->xml) ? 'Yes' : 'No') . "\n";
    echo "QR Generated: " . ($invoice->qr_generado ? 'Yes' : 'No') . "\n";
    echo "XML length: " . strlen($invoice->xml ?? '') . " characters\n\n";
    
    // Test direct QR generation
    echo "Testing direct QR generation...\n";
    try {
        $result = $invoice->generarYGuardarQR();
        echo "Direct generation result: " . ($result ? 'Success' : 'Failed') . "\n";
        
        // Check updated value
        $invoice->refresh();
        echo "After direct generation - QR Generated: " . ($invoice->qr_generado ? 'Yes' : 'No') . "\n\n";
        
    } catch (Exception $e) {
        echo "Error in direct generation: " . $e->getMessage() . "\n\n";
    }
    
    // Test if QR can be generated at all
    echo "Testing QR generation method...\n";
    try {
        $qrData = $invoice->generarCodigoQR();
        echo "QR generation result: " . ($qrData ? 'Success (' . strlen($qrData) . ' bytes)' : 'Failed') . "\n";
    } catch (Exception $e) {
        echo "Error in QR generation: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "No invoices with XML found.\n";
}

echo "\n=== Debug Complete ===\n";
