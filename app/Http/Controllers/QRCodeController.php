<?php

namespace App\Http\Controllers;

use App\Models\FacturaElectronica;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use App\Services\QrCodeService;

class QRCodeController extends Controller
{    /**
     * Genera y muestra el código QR para una factura electrónica
     *
     * @param Request $request
     * @param FacturaElectronica $factura
     * @return \Illuminate\Http\Response
     */
    public function mostrarQR(Request $request, FacturaElectronica $factura)
    {
        try {
            Log::info("Generating QR for invoice CDC: {$factura->cdc}");
            
            // Verificar que la factura tenga los datos mínimos necesarios
            if (empty($factura->cdc) || empty($factura->ruc_receptor)) {
                Log::error("Missing critical data for QR generation: CDC={$factura->cdc}, RUC={$factura->ruc_receptor}");
                return $this->generateErrorQR('Datos insuficientes para generar QR');
            }
            
            // Extraer el DigestValue del XML si está disponible
            $digestValue = '';
            if (!empty($factura->xml)) {
                if (preg_match('/<DigestValue>(.*?)<\/DigestValue>/', $factura->xml, $matches)) {
                    $digestValue = $matches[1];
                    Log::info("DigestValue extracted from XML: {$digestValue}");
                } else {
                    // Si no hay DigestValue, generamos un hash del XML
                    $digestValue = substr(hash('sha256', $factura->xml), 0, 28);
                    Log::info("DigestValue calculated from XML: {$digestValue}");
                }
            } else {
                // Si no hay XML, usamos un hash del CDC
                $digestValue = substr(hash('sha256', $factura->cdc), 0, 28);
                Log::warning("No XML available, using CDC hash: {$digestValue}");
            }

            // Contar items en el XML o usar un valor por defecto
            $itemCount = 1;
            if (!empty($factura->xml) && strpos($factura->xml, '<gCamItem>') !== false) {
                $itemCount = substr_count($factura->xml, '<gCamItem>');
                Log::info("Items counted from XML: {$itemCount}");
            }            // Preparamos los datos para el QR según especificaciones SIFEN
            $qrParams = [
                'nVersion=150',                                // Versión SIFEN
                'Id=' . $factura->cdc,                        // CDC completo
                'dFeEmiDE=' . $factura->fecha->format('Y-m-d'), // Fecha emisión ISO
                'dRucRec=' . $factura->ruc_receptor,          // RUC receptor
                'dTotGralOpe=' . number_format($factura->total, 0, '', ''), // Monto total sin separadores
                'dTotIVA=' . number_format($factura->impuesto, 0, '', ''), // IVA total sin separadores
                'cItems=' . $itemCount,                       // Cantidad de ítems
                'DigestValue=' . $digestValue,                // Valor del digest
                'IdCSC=' . config('facturacion_electronica.id_csc', '0001') // ID del CSC
            ];
            
            // Generamos la URL completa para el QR (URL de consulta de SIFEN)
            $baseUrl = 'https://ekuatia.set.gov.py/consultas/qr';
            $qrString = $baseUrl . '?' . implode('&', $qrParams);
            Log::info("QR string generated: {$qrString}");
            
            // Generamos y devolvemos el QR usando nuestro servicio que maneja la compatibilidad con GD
            $qrImage = QrCodeService::generate($qrString, 'png', 200);
            
            if ($qrImage) {
                Log::info("QR generated successfully for CDC {$factura->cdc} (" . strlen($qrImage) . " bytes)");
                return response($qrImage)
                    ->header('Content-Type', 'image/png')
                    ->header('Cache-Control', 'public, max-age=3600') // Cache por 1 hora
                    ->header('Content-Disposition', 'inline; filename="qr_' . $factura->cdc . '.png"');
            } else {
                // Si falló la generación PNG, intentar SVG
                Log::warning("PNG QR generation failed, trying SVG fallback");
                $qrImage = QrCodeService::generate($qrString, 'svg', 200);
                if ($qrImage) {
                    Log::info("SVG QR generated as fallback for CDC {$factura->cdc}");
                    return response($qrImage)
                        ->header('Content-Type', 'image/svg+xml')
                        ->header('Cache-Control', 'public, max-age=3600');
                }
                
                Log::error("Both PNG and SVG QR generation failed for CDC {$factura->cdc}");
                return $this->generateErrorQR('Error al generar código QR');
            }
        } catch (\Exception $e) {
            Log::error('Error al generar QR en QRCodeController: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return $this->generateErrorQR('QR no disponible');
        }    }
    
    /**
     * Generate an error QR code image
     *
     * @param string $message Error message to display
     * @return \Illuminate\Http\Response
     */
    private function generateErrorQR(string $message)
    {
        try {
            $errorImage = $this->generateErrorImage($message);
            return response($errorImage)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        } catch (\Exception $e) {
            Log::error('Error generating error QR image: ' . $e->getMessage());
            // Return a minimal response
            return response('QR Error', 500)->header('Content-Type', 'text/plain');
        }
    }
    
    /**
     * Genera una imagen de error simple con texto
     *
     * @param string $text
     * @return string
     */
    private function generateErrorImage($text)
    {
        // Creamos una imagen básica con el texto de error
        $image = imagecreate(200, 200);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 255, 0, 0);
        
        // Centramos el texto en la imagen
        $fontFile = 5; // Font built-in
        imagestring($image, $fontFile, 30, 90, $text, $textColor);
        
        // Capturamos la salida
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        
        // Limpiamos recursos
        imagedestroy($image);
        
        return $imageData;
    }
}
