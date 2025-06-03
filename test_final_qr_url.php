<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacturaElectronica;
use App\Http\Controllers\QRCodeController;
use Illuminate\Http\Request;

echo "=== Testing QR URL Generation ===\n\n";

// Usar datos del ejemplo
$testData = [
    'cdc' => '01800695631001001000000120250603396154022058220582',
    'ruc_receptor' => '500461-7',
    'total' => 20000,
    'impuesto' => 2000,
    'fecha' => '2025-06-03',
    'items' => 1,
    'csc_id' => '0001'
];

echo "Testing with fixed data:\n";
echo "  CDC: {$testData['cdc']}\n";
echo "  RUC Receptor: {$testData['ruc_receptor']}\n";
echo "  Total: {$testData['total']}\n";
echo "  IVA: {$testData['impuesto']}\n";
echo "  Fecha: {$testData['fecha']}\n\n";
    
    // Extract DigestValue from XML
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
    
    // Count items
    $itemCount = 1;
    if (!empty($invoice->xml) && strpos($invoice->xml, '<gCamItem>') !== false) {
        $itemCount = substr_count($invoice->xml, '<gCamItem>');
    }
      // Generate QR data as specified by SIFEN
    $qrData = "https://ekuatia.set.gov.py/consultas/qr?nVersion=150&Id=" . $testData['cdc'];
    
    $qrString = implode('&', $qrData);
    
    echo "Generated QR String:\n";
    echo $qrString . "\n\n";
    
    // Check if it looks like a proper SIFEN URL
    echo "QR String Analysis:\n";
    echo "  Length: " . strlen($qrString) . " characters\n";
    echo "  Contains Version: " . (strpos($qrString, 'nVersion=150') !== false ? 'YES' : 'NO') . "\n";
    echo "  Contains CDC: " . (strpos($qrString, "Id={$invoice->cdc}") !== false ? 'YES' : 'NO') . "\n";
    echo "  Contains DigestValue: " . (strpos($qrString, 'DigestValue=') !== false ? 'YES' : 'NO') . "\n";
    echo "  Contains CSC ID: " . (strpos($qrString, 'IdCSC=') !== false ? 'YES' : 'NO') . "\n\n";
    
    // Generate the actual QR and check size
    $qrImage = \App\Services\QrCodeService::generate($qrString, 'png', 200);
    if ($qrImage) {
        echo "QR Image Generation:\n";
        echo "  Size: " . strlen($qrImage) . " bytes\n";
        echo "  PNG Header: " . (substr($qrImage, 0, 8) === "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A" ? 'VALID' : 'INVALID') . "\n";
        
        // Save for inspection
        file_put_contents('final_qr_test.png', $qrImage);
        echo "  Saved as: final_qr_test.png\n";
    } else {
        echo "  QR generation FAILED\n";
    }
    
} else {
    echo "No invoices found for testing\n";
}

echo "\n=== Test Complete ===\n";
