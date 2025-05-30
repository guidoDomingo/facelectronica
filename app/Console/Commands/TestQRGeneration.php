<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FacturaElectronica;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\QrCodeService;

class TestQRGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:qr-generation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test QR code generation for electronic invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Testing QR Code Generation ===');
        
        // Test available methods
        $this->info('0. Checking available QR generation methods...');
        $methods = QrCodeService::getAvailableMethods();
        $this->info('Available methods: ' . implode(', ', $methods));
        $this->info('ImageMagick available: ' . (QrCodeService::hasImageMagick() ? 'Yes' : 'No'));
        $this->info('GD available: ' . (QrCodeService::hasGD() ? 'Yes' : 'No'));
        $this->info('');
        
        // Test basic QR generation
        $this->info('1. Testing basic QR generation with our service...');
        try {
            $basicQR = QrCodeService::generate('Hello World', 'png', 200);
            if ($basicQR) {
                $this->info('✓ Basic QR generation successful (' . strlen($basicQR) . ' bytes)');
                file_put_contents('test_service_basic_qr.png', $basicQR);
                $this->info('✓ QR saved to test_service_basic_qr.png');
            } else {
                $this->error('✗ Basic QR generation returned null');
            }
        } catch (\Exception $e) {
            $this->error('✗ Basic QR generation failed: ' . $e->getMessage());
            
            // Try SVG as fallback
            try {
                $this->info('Trying SVG format as fallback...');
                $basicQR = QrCodeService::generate('Hello World', 'svg', 200);
                if ($basicQR) {
                    $this->info('✓ SVG QR generation successful (' . strlen($basicQR) . ' bytes)');
                } else {
                    $this->error('✗ SVG QR generation also returned null');
                    return 1;
                }
            } catch (\Exception $e2) {
                $this->error('✗ SVG QR generation also failed: ' . $e2->getMessage());
                return 1;
            }
        }
        
        // Find an invoice with XML
        $this->info('2. Finding invoice with XML...');
        $invoice = FacturaElectronica::whereNotNull('xml')->first();
        
        if (!$invoice) {
            $this->error('✗ No invoices with XML found');
            return 1;
        }
        
        $this->info("✓ Found invoice ID: {$invoice->id}, CDC: {$invoice->cdc}");
        
        // Test QR generation using controller logic
        $this->info('3. Testing QR generation with invoice data...');
        try {
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
            $this->info("QR String: $qrString");
            
            $qrImage = QrCodeService::generate($qrString, 'png', 200);
            if ($qrImage) {
                $this->info('✓ Invoice QR generation successful (' . strlen($qrImage) . ' bytes)');
                
                // Save for verification
                file_put_contents('test_invoice_qr.png', $qrImage);
                $this->info('✓ QR saved to test_invoice_qr.png');
            } else {
                $this->error('✗ Invoice QR generation returned null');
            }
            
        } catch (\Exception $e) {
            $this->error('✗ Invoice QR generation failed: ' . $e->getMessage());
            return 1;
        }
        
        // Test model method
        $this->info('4. Testing model QR generation method...');
        try {
            $modelQR = $invoice->generarCodigoQR();
            if ($modelQR) {
                $this->info('✓ Model QR generation successful (' . strlen($modelQR) . ' bytes)');
            } else {
                $this->error('✗ Model QR generation returned null');
            }
        } catch (\Exception $e) {
            $this->error('✗ Model QR generation failed: ' . $e->getMessage());
        }
        
        // Test automatic QR generation
        $this->info('5. Testing automatic QR generation...');
        try {
            $result = $invoice->generarYGuardarQR();
            if ($result) {
                $invoice->refresh();
                $this->info('✓ Automatic QR generation successful');
                $this->info("✓ QR Generated flag: " . ($invoice->qr_generado ? 'true' : 'false'));
            } else {
                $this->error('✗ Automatic QR generation failed');
            }
        } catch (\Exception $e) {
            $this->error('✗ Automatic QR generation failed: ' . $e->getMessage());
        }
        
        $this->info('=== Test Complete ===');
        return 0;
    }
}
