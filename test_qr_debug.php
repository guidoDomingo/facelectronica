<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FacturaElectronica;
use App\Services\QrCodeService;

echo "=== QR Debug Test ===\n\n";

// Get a factura with XML
$factura = FacturaElectronica::whereNotNull('xml')->first();

if ($factura) {
    echo "Factura encontrada:\n";
    echo "ID: {$factura->id}\n";
    echo "CDC: {$factura->cdc}\n";
    echo "Fecha: {$factura->fecha->format('Y-m-d')}\n";
    echo "RUC Receptor: {$factura->ruc_receptor}\n";
    echo "Total: {$factura->total}\n";
    echo "IVA: {$factura->impuesto}\n";
    echo "XML Length: " . strlen($factura->xml) . " chars\n";
    echo "QR Generated: " . ($factura->qr_generado ? 'Si' : 'No') . "\n\n";
    
    // Extract DigestValue from XML
    $digestValue = '';
    if (preg_match('/<DigestValue>(.*?)<\/DigestValue>/', $factura->xml, $matches)) {
        $digestValue = $matches[1];
        echo "DigestValue from XML: $digestValue\n";
    } else {
        $digestValue = substr(hash('sha256', $factura->xml), 0, 28);
        echo "DigestValue calculated: $digestValue\n";
    }
    
    // Count items
    $itemCount = 1;
    if (strpos($factura->xml, '<gCamItem>') !== false) {
        $itemCount = substr_count($factura->xml, '<gCamItem>');
    }
    echo "Items count: $itemCount\n\n";
    
    // Generate QR data like the model does
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
    echo "QR String: $qrString\n\n";
    
    // Test QR generation
    echo "Testing QR generation:\n";
    
    // 1. Test model method
    echo "1. Model method: ";
    try {
        $modelQR = $factura->generarCodigoQR();
        echo $modelQR ? "SUCCESS (" . strlen($modelQR) . " bytes)" : "FAILED";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage();
    }
    echo "\n";
    
    // 2. Test service directly
    echo "2. Service directly: ";
    try {
        $directQR = QrCodeService::generate($qrString, 'png', 200);
        echo $directQR ? "SUCCESS (" . strlen($directQR) . " bytes)" : "FAILED";
        
        if ($directQR) {
            file_put_contents('debug_qr.png', $directQR);
            echo " - Saved to debug_qr.png";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage();
    }
    echo "\n";
    
    // 3. Test automatic generation
    echo "3. Auto generation: ";
    try {
        $result = $factura->generarYGuardarQR();
        echo $result ? "SUCCESS" : "FAILED";
        
        $factura->refresh();
        echo " - QR Generated flag: " . ($factura->qr_generado ? 'Si' : 'No');
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage();
    }
    echo "\n";
    
} else {
    echo "No se encontraron facturas con XML\n";
}

echo "\n=== Test Complete ===\n";
