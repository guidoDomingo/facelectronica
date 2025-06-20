<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FacturaElectronica;

echo "Total facturas: " . FacturaElectronica::count() . PHP_EOL;

$facturas = FacturaElectronica::latest()->take(5)->get(['id', 'cdc', 'estado', 'created_at']);

foreach ($facturas as $factura) {
    echo "ID: {$factura->id} - CDC: {$factura->cdc} - Estado: {$factura->estado} - Creada: {$factura->created_at}" . PHP_EOL;
}

if ($facturas->count() > 0) {
    echo "\nPrimera factura disponible para testing: " . $facturas->first()->id . PHP_EOL;
} else {
    echo "\nNo hay facturas en la base de datos. Necesitamos crear una para probar." . PHP_EOL;
}
