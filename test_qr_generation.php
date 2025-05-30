<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FacturaElectronica;

echo "=== Testing QR Code Generation ===\n\n";

// Check existing invoices with XML but no QR generated
echo "1. Checking existing invoices with XML but no QR generated:\n";
$count = FacturaElectronica::whereNotNull('xml')
    ->where(function($query) {
        $query->where('qr_generado', false)->orWhereNull('qr_generado');
    })
    ->count();
echo "Found: $count invoices\n\n";

// Show first few invoices
echo "2. First 5 invoices with XML:\n";
$invoices = FacturaElectronica::whereNotNull('xml')->limit(5)->get(['id', 'cdc', 'numero', 'qr_generado']);
foreach($invoices as $invoice) {
    echo "ID: {$invoice->id}, CDC: {$invoice->cdc}, Numero: {$invoice->numero}, QR Generated: " . ($invoice->qr_generado ? 'Yes' : 'No') . "\n";
}
echo "\n";

// Test automatic QR generation by updating an existing invoice
if ($invoices->count() > 0) {
    $testInvoice = $invoices->first();
    echo "3. Testing automatic QR generation with invoice ID: {$testInvoice->id}\n";
    
    // Force save to trigger the event
    $testInvoice->observacion = 'Test QR generation - ' . now();
    $testInvoice->save();
    
    // Refresh from database
    $testInvoice->refresh();
    echo "After save - QR Generated: " . ($testInvoice->qr_generado ? 'Yes' : 'No') . "\n";
}

echo "\n=== Test Complete ===\n";
