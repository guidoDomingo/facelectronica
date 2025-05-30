<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FacturaElectronica;

echo "=== Detailed QR Debug ===\n\n";

// Get a specific invoice
$invoice = FacturaElectronica::where('xml', '!=', null)->first();

if ($invoice) {
    echo "Invoice ID: {$invoice->id}\n";
    echo "CDC: {$invoice->cdc}\n";
    echo "Fecha: {$invoice->fecha}\n";
    echo "RUC Receptor: {$invoice->ruc_receptor}\n";
    echo "Total: {$invoice->total}\n";
    echo "Impuesto: {$invoice->impuesto}\n\n";
    
    // Extract XML data manually to debug
    echo "=== XML Analysis ===\n";
    $xml = $invoice->xml;
    echo "XML Length: " . strlen($xml) . "\n";
    
    // Check for DigestValue
    if (strpos($xml, '<DigestValue>') !== false) {
        preg_match('/<DigestValue>(.*?)<\/DigestValue>/', $xml, $matches);
        if (isset($matches[1])) {
            echo "DigestValue found: " . $matches[1] . "\n";
        }
    } else {
        echo "No DigestValue found, will use hash\n";
    }
    
    // Count items
    $itemCount = 1;
    if (strpos($xml, '<gCamItem>') !== false) {
        $itemCount = substr_count($xml, '<gCamItem>');
    }
    echo "Item count: $itemCount\n\n";
    
    // Try to generate QR string manually
    echo "=== QR String Generation ===\n";
    try {
        $digestValue = '';
        if (strpos($xml, '<DigestValue>') !== false) {
            preg_match('/<DigestValue>(.*?)<\/DigestValue>/', $xml, $matches);
            if (isset($matches[1])) {
                $digestValue = $matches[1];
            }
        } else {
            $digestValue = hash('sha256', $xml);
        }
        
        $qrData = [
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
        
        $qrString = implode('&', $qrData);
        echo "QR String: $qrString\n\n";
        
        // Try to generate QR with different methods
        echo "=== QR Generation Tests ===\n";
        
        // Test 1: Direct QrCode class
        try {
            echo "Test 1: Using QrCode class directly...\n";
            $qr1 = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(200)
                ->errorCorrection('H')
                ->generate($qrString);
            echo "Success! Generated " . strlen($qr1) . " bytes\n";
        } catch (\Exception $e) {
            echo "Failed: " . $e->getMessage() . "\n";
        }
        
        // Test 2: Using QR helper function if exists
        try {
            echo "Test 2: Using QR helper...\n";
            if (function_exists('QrCode')) {
                $qr2 = QrCode::format('png')->size(200)->generate($qrString);
                echo "Success! Generated " . strlen($qr2) . " bytes\n";
            } else {
                echo "QR helper function not available\n";
            }
        } catch (\Exception $e) {
            echo "Failed: " . $e->getMessage() . "\n";
        }
        
    } catch (\Exception $e) {
        echo "Error in manual generation: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "No invoices with XML found.\n";
}

echo "\n=== Debug Complete ===\n";
