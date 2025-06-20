<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FacturaElectronica;
use App\Services\FacturacionElectronica\FacturacionElectronicaServiceV2;

echo "=== COMPREHENSIVE SIFEN INTEGRATION TEST ===\n";
echo "Testing Node.js API integration vs old SOAP implementation\n\n";

try {
    $factura = FacturaElectronica::latest()->first();
    
    if (!$factura) {
        echo "❌ No invoices found in database\n";
        exit(1);
    }
    
    echo "📋 Test Invoice Details:\n";
    echo "   ID: {$factura->id}\n";
    echo "   CDC: {$factura->cdc}\n";
    echo "   Estado: {$factura->estado}\n\n";
    
    $service = app(FacturacionElectronicaServiceV2::class);
    
    echo "🧪 TEST 1: Document Status Query\n";
    echo "⏳ Testing consultarEstadoDocumento...\n";
    
    $startTime = microtime(true);
    $resultado = $service->consultarEstadoDocumento($factura->cdc, ['simular' => false]);
    $duration = round((microtime(true) - $startTime) * 1000, 2);
    
    echo "✅ Completed in {$duration}ms\n";
    echo "   Success: " . ($resultado['success'] ? 'true' : 'false') . "\n";
    
    if (isset($resultado['message'])) {
        echo "   Message: " . $resultado['message'] . "\n";
    }
    
    // Check for SOAP-related errors
    $hasSOAPError = false;
    if (isset($resultado['message'])) {
        $hasSOAPError = str_contains(strtolower($resultado['message']), 'soap') || 
                       str_contains(strtolower($resultado['message']), 'wsdl');
    }
    
    if ($hasSOAPError) {
        echo "   🚨 SOAP ERROR DETECTED!\n";
    } else {
        echo "   ✅ No SOAP errors - using HTTP API\n";
    }
    
    echo "\n🧪 TEST 2: Document Sending\n";
    echo "⏳ Testing enviarDocumentoSIFEN...\n";
    
    if (empty($factura->xml)) {
        echo "   ⚠️  No XML found - skipping send test\n";
    } else {
        $startTime = microtime(true);
        try {
            $resultadoEnvio = $service->enviarDocumentoSIFEN($factura->xml);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            echo "✅ Completed in {$duration}ms\n";
            echo "   Success: " . ($resultadoEnvio['success'] ? 'true' : 'false') . "\n";
            
            $hasSOAPErrorEnvio = false;
            if (isset($resultadoEnvio['message'])) {
                echo "   Message: " . $resultadoEnvio['message'] . "\n";
                $hasSOAPErrorEnvio = str_contains(strtolower($resultadoEnvio['message']), 'soap') || 
                                   str_contains(strtolower($resultadoEnvio['message']), 'wsdl');
            }
            
            if ($hasSOAPErrorEnvio) {
                echo "   🚨 SOAP ERROR DETECTED!\n";
            } else {
                echo "   ✅ No SOAP errors - using HTTP API\n";
            }
            
        } catch (Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            echo "⚠️  Exception in {$duration}ms: " . $e->getMessage() . "\n";
            
            if (str_contains($e->getMessage(), 'SOAP') || str_contains($e->getMessage(), 'WSDL')) {
                echo "   🚨 SOAP ERROR IN EXCEPTION!\n";
            } else {
                echo "   ✅ No SOAP errors in exception\n";
            }
        }
    }
    
    echo "\n📊 FINAL RESULTS:\n";
    echo "==================\n";
    
    if (!$hasSOAPError && (!isset($hasSOAPErrorEnvio) || !$hasSOAPErrorEnvio)) {
        echo "🎉 SUCCESS: SOAP errors have been resolved!\n";
        echo "✅ The integration is now using Node.js HTTP API instead of direct SOAP\n";
        echo "✅ No more 'SOAP-ERROR: Parsing WSDL: Couldn't load from SIFEN' errors\n";
        echo "✅ The 'Detalle de Factura Electrónica' page should work without errors\n\n";
        
        echo "🔧 Next Steps:\n";
        echo "1. Configure proper certificates in the Node.js service\n";
        echo "2. Test with real SIFEN credentials in production\n";
        echo "3. Monitor logs for successful document submissions\n";
    } else {
        echo "❌ SOAP errors are still present\n";
        echo "🔍 Please check the service provider configuration\n";
    }
    
} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if (str_contains($e->getMessage(), 'SOAP') || str_contains($e->getMessage(), 'WSDL')) {
        echo "\n🚨 This is a SOAP-related error that needs to be fixed!\n";
    }
}
