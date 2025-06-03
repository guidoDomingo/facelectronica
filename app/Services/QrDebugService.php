<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;

class QrDebugService
{
    /**
     * Genera un código QR simple para depuración
     */
    public static function generateSimple(string $data, int $size = 300): ?string
    {
        try {
            // 1. Crear QR básico
            $qr = QrCode::create($data)
                ->setSize($size)
                ->setMargin(1);
            
            // 2. Generar PNG
            $result = (new PngWriter())->write($qr);
            
            // 3. Obtener datos en formato data URI
            $dataUri = $result->getDataUri();
            
            // 4. Convertir data URI a datos binarios
            $base64Data = explode(',', $dataUri)[1];
            return base64_decode($base64Data);
            
        } catch (\Exception $e) {
            error_log("Error en QrDebugService: " . $e->getMessage());
            return null;
        }
    }
}
