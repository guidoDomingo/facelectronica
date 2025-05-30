<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FacturaElectronica;
use App\Http\Controllers\QRCodeController;
use Illuminate\Http\Request;

echo "=== Testing QR Controller ===\n\n";

// Get an invoice
$invoice = FacturaElectronica::whereNotNull('xml')->first();

if ($invoice) {
    echo "Testing with invoice ID: {$invoice->id}, CDC: {$invoice->cdc}\n";
    
    try {
        // Create controller instance
        $controller = new QRCodeController();
        
        // Create a mock request
        $request = new Request();
        
        echo "Calling mostrarQR method...\n";
        $response = $controller->mostrarQR($request, $invoice);
        
        echo "Response status: " . $response->getStatusCode() . "\n";
        echo "Response headers:\n";
        foreach ($response->headers->all() as $header => $values) {
            echo "  $header: " . implode(', ', $values) . "\n";
        }
        
        $content = $response->getContent();
        echo "Response content length: " . strlen($content) . " bytes\n";
        
        // Check if it's PNG content
        if (substr($content, 0, 8) === "\x89PNG\r\n\x1a\n") {
            echo "✓ Valid PNG response!\n";
            file_put_contents('test_controller_qr.png', $content);
            echo "✓ QR saved to test_controller_qr.png\n";
        } elseif (strpos($content, '<svg') !== false) {
            echo "✓ Valid SVG response!\n";
            file_put_contents('test_controller_qr.svg', $content);
            echo "✓ QR saved to test_controller_qr.svg\n";
        } else {
            echo "Response content preview: " . substr($content, 0, 100) . "...\n";
        }
        
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
} else {
    echo "No invoices with XML found\n";
}

echo "\n=== Test Complete ===\n";
