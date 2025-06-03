<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;

class QrCodeService
{
    /**
     * Genera código QR
     *
     * @param string $data Los datos a codificar en el QR
     * @param string $format El formato deseado ('png', 'svg')
     * @param int $size El tamaño del código QR
     * @return string|null Los datos del QR o null en caso de fallo
     */    public static function generate(string $data, string $format = 'png', int $size = 200): ?string
    {
        try {
            // Validación básica
            if (empty($data)) {
                Log::error('Datos vacíos para QR');
                return null;
            }
            
            // Normalizar tamaño
            $size = max(100, min(1000, $size));
            
            // Crear instancia de QR con parámetros optimizados
            $qr = QrCode::create(trim($data))
                ->setSize($size)
                ->setMargin(1)
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::High) // Use static level
                ->setForegroundColor(new Color(0, 0, 0))
                ->setBackgroundColor(new Color(255, 255, 255))
                ->setEncoding(new Encoding('UTF-8'));

            Log::info("QR preparado para longitud de datos: " . strlen($data));

            // Generar QR
            $writer = $format === 'svg' ? new SvgWriter() : new PngWriter();
            $result = $writer->write($qr);
            
            // Obtener data URI
            $dataUri = $result->getDataUri();
            
            // Para PNG, convertir a binario
            if ($format !== 'svg') {
                $pngData = base64_decode(explode(',', $dataUri)[1]);
                
                if (strlen($pngData) > 100 && substr($pngData, 0, 8) === "\x89PNG\r\n\x1a\n") {
                    Log::info("QR generado exitosamente: " . strlen($pngData) . " bytes");
                    return $pngData;
                }
                
                throw new \Exception('El QR generado no es un PNG válido');
            }

            return $dataUri;

        } catch (\Exception $e) {
            Log::error("Error en generación de QR: " . $e->getMessage());
            
            if ($format !== 'svg') {
                return self::generateErrorImage($size);
            }
            
            return null;
        }
    }

    private static function generateErrorImage(int $size): ?string
    {
        try {
            $image = imagecreatetruecolor($size, $size);
            
            if (!$image) {
                return null;
            }

            $white = imagecolorallocate($image, 255, 255, 255);
            $red = imagecolorallocate($image, 255, 0, 0);

            // Fondo blanco
            imagefilledrectangle($image, 0, 0, $size-1, $size-1, $white);
            
            // Borde rojo
            imagerectangle($image, 0, 0, $size-1, $size-1, $red);
            imagerectangle($image, 1, 1, $size-2, $size-2, $red);
            
            // Texto centrado
            $text = "QR no disponible";
            $font = 5;
            $textWidth = imagefontwidth($font) * strlen($text);
            $textHeight = imagefontheight($font);
            
            $x = ($size - $textWidth) / 2;
            $y = ($size - $textHeight) / 2;
            
            imagestring($image, $font, $x, $y, $text, $red);
            
            // Generar PNG
            ob_start();
            imagepng($image);
            $pngData = ob_get_clean();
            imagedestroy($image);

            return $pngData;

        } catch (\Exception $e) {
            Log::error("Error generando imagen de error: " . $e->getMessage());
            return null;
        }
    }
}
