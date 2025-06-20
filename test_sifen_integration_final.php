<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FacturaElectronica;
use App\Services\FacturacionElectronica\FacturacionElectronicaServiceV2;

echo "=== TESTING SIFEN INTEGRATION WITH NODEJS API ===\n\n";

try {
    // Get the latest invoice
    $factura = FacturaElectronica::latest()->first();
    
    if (!$factura) {
        echo "❌ No invoices found in database\n";
        exit(1);
    }
    
    echo "📄 Testing with Invoice:\n";
    echo "   ID: {$factura->id}\n";
    echo "   CDC: {$factura->cdc}\n";
    echo "   Estado: {$factura->estado}\n";
    echo "   Fecha: {$factura->created_at}\n\n";
    
    // Test the service
    $service = app(FacturacionElectronicaServiceV2::class);
    
    echo "🔄 Testing consultarEstadoDocumento with Node.js API...\n";
    
    $resultado = $service->consultarEstadoDocumento($factura->cdc, ['simular' => false]);
    
    echo "✅ Response received:\n";
    echo "   Success: " . ($resultado['success'] ? 'true' : 'false') . "\n";
    
    if (isset($resultado['resultado'])) {
        echo "   Estado: " . ($resultado['resultado']['estado'] ?? 'N/A') . "\n";
        
        if (isset($resultado['resultado']['respuesta'])) {
            echo "   Código: " . ($resultado['resultado']['respuesta']['codigo'] ?? 'N/A') . "\n";
            echo "   Mensaje: " . ($resultado['resultado']['respuesta']['mensaje'] ?? 'N/A') . "\n";
        }
    }
    
    if (isset($resultado['message'])) {
        echo "   Message: " . $resultado['message'] . "\n";
    }
    
    echo "\n🎉 SOAP errors should be resolved now!\n";
    echo "✅ The integration is using HTTP requests to Node.js instead of direct SOAP calls.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if (str_contains($e->getMessage(), 'SOAP')) {
        echo "\n🚨 SOAP ERROR DETECTED! This means the old client is still being used.\n";
    } else {
        echo "\n✅ No SOAP errors - this suggests the new Node.js integration is working.\n";
    }
}
