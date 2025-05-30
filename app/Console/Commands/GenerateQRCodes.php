<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FacturaElectronica;
use Illuminate\Support\Facades\Log;

class GenerateQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'factura:generate-qrs {--force : Force regeneration of existing QRs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate QR codes for electronic invoices that don\'t have them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting QR code generation for electronic invoices...');
        
        $forceRegeneration = $this->option('force');
        
        // Get invoices that need QR generation
        $query = FacturaElectronica::whereNotNull('xml');
        
        if (!$forceRegeneration) {
            $query->where(function($q) {
                $q->where('qr_generado', false)->orWhereNull('qr_generado');
            });
        }
        
        $facturas = $query->get();
        
        if ($facturas->isEmpty()) {
            $this->info('No invoices found that need QR generation.');
            return 0;
        }
        
        $this->info("Found {$facturas->count()} invoices to process.");
        
        $successful = 0;
        $failed = 0;
        
        $progressBar = $this->output->createProgressBar($facturas->count());
        $progressBar->start();
        
        foreach ($facturas as $factura) {
            try {
                $result = $factura->generarYGuardarQR();
                
                if ($result) {
                    $successful++;
                    $this->line("\n✓ QR generated for CDC: {$factura->cdc}");
                } else {
                    $failed++;
                    $this->line("\n✗ Failed to generate QR for CDC: {$factura->cdc}");
                }
                
            } catch (\Exception $e) {
                $failed++;
                $this->line("\n✗ Error generating QR for CDC {$factura->cdc}: " . $e->getMessage());
                Log::error("QR generation error for CDC {$factura->cdc}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        
        $this->newLine(2);
        $this->info("QR generation completed!");
        $this->info("✓ Successful: {$successful}");
        $this->info("✗ Failed: {$failed}");
        
        if ($failed > 0) {
            $this->warn("Some QR codes failed to generate. Check the logs for details.");
            return 1;
        }
        
        return 0;
    }
}
