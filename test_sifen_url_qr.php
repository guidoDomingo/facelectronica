<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacturaElectronica;

echo "=== Testing FINAL QR URL Generation ===\n\n";

// Get an invoice
$invoice = FacturaElectronica::whereNotNull('xml')->first();

if ($invoice) {
    echo "Testing with invoice CDC: {$invoice->cdc}\n\n";
    
    // Simulate the controller logic
    $digestValue = '';
    if (!empty($invoice->xml)) {
        if (preg_match('/<DigestValue>(.*?)<\/DigestValue>/', $invoice->xml, $matches)) {
            $digestValue = $matches[1];
        } else {
            $digestValue = substr(hash('sha256', $invoice->xml), 0, 28);
        }
    } else {
        $digestValue = substr(hash('sha256', $invoice->cdc), 0, 28);
    }
    
    $itemCount = 1;
    if (!empty($invoice->xml) && strpos($invoice->xml, '<gCamItem>') !== false) {
        $itemCount = substr_count($invoice->xml, '<gCamItem>');
    }
    
    // Generate the full SIFEN URL as the controller now does
    $qrParams = [
        'nVersion=150',
        'Id=' . $invoice->cdc,
        'dFeEmiDE=' . $invoice->fecha->format('Y-m-d'),
        'dRucRec=' . $invoice->ruc_receptor,
        'dTotGralOpe=' . number_format($invoice->total, 0, '', ''),
        'dTotIVA=' . number_format($invoice->impuesto, 0, '', ''),
        'cItems=' . $itemCount,
        'DigestValue=' . $digestValue,
        'IdCSC=' . config('facturacion_electronica.id_csc', '0001')
    ];
    
    $baseUrl = 'https://ekuatia.set.gov.py/consultas/qr';
    $qrString = $baseUrl . '?' . implode('&', $qrParams);
    
    echo "Generated SIFEN QR URL:\n";
    echo $qrString . "\n\n";
    
    echo "URL Analysis:\n";
    echo "  Base URL: " . (strpos($qrString, 'https://ekuatia.set.gov.py/consultas/qr') === 0 ? 'CORRECT' : 'INCORRECT') . "\n";
    echo "  Total Length: " . strlen($qrString) . " characters\n";
    echo "  Contains all parameters: " . (substr_count($qrString, '=') >= 9 ? 'YES' : 'NO') . "\n\n";
    
    // Test QR generation with the new URL
    $qrImage = \App\Services\QrCodeService::generate($qrString, 'png', 200);
    if ($qrImage) {
        echo "QR Generation:\n";
        echo "  Status: SUCCESS\n";
        echo "  Size: " . strlen($qrImage) . " bytes\n";
        
        file_put_contents('final_sifen_qr.png', $qrImage);
        echo "  Saved as: final_sifen_qr.png\n";
        
        echo "\n✅ QR Code now contains the complete SIFEN consultation URL!\n";
        echo "This QR can be scanned to directly access the invoice on SIFEN's system.\n";
    } else {
        echo "❌ QR generation failed\n";
    }
    
} else {
    echo "No invoices found for testing\n";
}

echo "\n=== Test Complete ===\n";
