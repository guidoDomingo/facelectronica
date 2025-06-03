<?php

namespace App\Http\Controllers;

use App\Models\FacturaElectronica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\QrCodeService;

class QRCodeController extends Controller
{
    /**
     * Genera y muestra el código QR para una factura electrónica
     *
     * @param Request $request
     * @param FacturaElectronica $factura
     * @return \Illuminate\Http\Response
     */
    public function mostrarQR(Request $request, FacturaElectronica $factura)
    {
        try {
            // Force regeneration if requested
            if ($request->has('regenerate')) {
                $factura->qr_generado = false;
                $factura->save();
            }

            // Verificar datos críticos
            if (empty($factura->cdc)) {
                Log::error("CDC no disponible para generar QR");
                throw new \Exception('CDC no disponible');
            }

            // Usar URL SIFEN simplificada
            $qrString = 'https://ekuatia.set.gov.py/consultas/qr?nVersion=150&Id=' . $factura->cdc;
            
            Log::info("Generando QR para CDC {$factura->cdc}");
            Log::debug("URL QR: {$qrString}");
            
            // Generar QR con tamaño óptimo
            $qrImage = QrCodeService::generate($qrString, 'png', 300);
            
            if (!$qrImage) {
                throw new \Exception('Generación de QR falló');
            }
            
            // Verificar formato PNG
            if (substr($qrImage, 0, 8) !== "\x89PNG\r\n\x1a\n") {
                throw new \Exception('QR generado no es un PNG válido');
            }
            
            Log::info("QR generado exitosamente: " . strlen($qrImage) . " bytes");

            // Retornar imagen con headers apropiados
            return response($qrImage)
                ->header('Content-Type', 'image/png')
                ->header('Content-Length', strlen($qrImage))
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
                
        } catch (\Exception $e) {
            Log::error('Error en mostrarQR: ' . $e->getMessage());
            
            // Generar imagen de error
            $errorImage = QrCodeService::generate('Error: ' . $e->getMessage(), 'png', 200);
            return response($errorImage ?: 'QR no disponible')
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'no-store');
        }
    }
}
