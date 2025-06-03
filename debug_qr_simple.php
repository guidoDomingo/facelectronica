<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Services\QrDebugService;

// URL de SIFEN para pruebas
$cdc = '01800695631001001000000120250603396154022058220582';
$url = "https://ekuatia.set.gov.py/consultas/qr?nVersion=150&Id=" . $cdc;

echo "Generando QR para URL:\n";
echo $url . "\n\n";

// Generar QR
$qrData = QrDebugService::generateSimple($url, 300);

if ($qrData === null) {
    echo "Error: No se pudo generar el QR\n";
    exit(1);
}

// Guardar archivo
$filename = 'debug_qr.png';
if (file_put_contents($filename, $qrData)) {
    echo "QR generado exitosamente en: $filename\n";
    echo "Tamaño: " . strlen($qrData) . " bytes\n";
    
    // Verificar formato PNG
    if (substr($qrData, 0, 8) === "\x89PNG\r\n\x1a\n") {
        echo "Formato: PNG válido\n";
    } else {
        echo "Advertencia: El archivo no parece ser un PNG válido\n";
    }
} else {
    echo "Error: No se pudo guardar el archivo\n";
}
