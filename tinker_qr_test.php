<?php

use App\Models\FacturaElectronica;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

// Get an invoice
$invoice = FacturaElectronica::whereNotNull('xml')->first();

if ($invoice) {
    echo "Testing QR generation for invoice ID: {$invoice->id}\n";
    echo "CDC: {$invoice->cdc}\n";
    
    try {
        // Test the basic QrCode facade
        $qr = QrCode::format('png')->size(200)->generate('Test QR');
        echo "Basic QR test: SUCCESS (" . strlen($qr) . " bytes)\n";
        
        // Test with invoice data (using controller logic)
        $digestValue = '';
        if (!empty($invoice->xml)) {
            if (preg_match('/<DigestValue>(.*?)<\/DigestValue>/', $invoice->xml, $matches)) {
                $digestValue = $matches[1];
            } else {
                $digestValue = substr(hash('sha256', $invoice->xml), 0, 28);
            }
        }
        
        $itemCount = 1;
        if (!empty($invoice->xml) && strpos($invoice->xml, '<gCamItem>') !== false) {
            $itemCount = substr_count($invoice->xml, '<gCamItem>');
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
        echo "QR String: $qrString\n";
        
        $qrImage = QrCode::format('png')->size(200)->generate($qrString);
        echo "Invoice QR test: SUCCESS (" . strlen($qrImage) . " bytes)\n";
        
        // Save the QR image
        file_put_contents('invoice_qr.png', $qrImage);
        echo "QR image saved to invoice_qr.png\n";
        
        // Now test the model method
        $modelQR = $invoice->generarCodigoQR();
        echo "Model QR test: " . ($modelQR ? "SUCCESS (" . strlen($modelQR) . " bytes)" : "FAILED") . "\n";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No invoices found\n";
}
