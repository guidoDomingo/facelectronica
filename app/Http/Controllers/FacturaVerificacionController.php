<?php

namespace App\Http\Controllers;

use App\Models\FacturaElectronica;
use App\Services\FacturacionElectronica\FacturacionElectronicaServiceV2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class FacturaVerificacionController extends Controller
{
    protected $facturacionService;
      public function __construct(FacturacionElectronicaServiceV2 $facturacionService)
    {
        $this->facturacionService = $facturacionService;
    }
      /**
     * Verifica el estado de una factura electrónica en SIFEN
     *
     * @param Request $request
     * @param FacturaElectronica $factura
     * @return \Illuminate\Http\Response
     */
    public function verificarEstadoSIFEN(Request $request, FacturaElectronica $factura)
    {
        try {
            // Verificar que la factura tenga un CDC
            if (empty($factura->cdc)) {
                return back()->with('error', 'La factura no tiene un CDC válido para consultar');
            }
            
            // Determinar si forzamos modo real o simulación
            $options = [];
            if ($request->has('modo')) {
                $options['simular'] = $request->modo === 'simular';
            }
            
            // Consultar estado en SIFEN
            $resultado = $this->facturacionService->consultarEstadoDocumento($factura->cdc, $options);
            
            // Verificar si hay error en la respuesta
            if (!isset($resultado['success']) || $resultado['success'] === false) {
                $mensajeError = isset($resultado['message']) ? $resultado['message'] : 'Error desconocido al consultar estado';
                
                // Registrar el error como un evento
                $factura->registrarEvento('consulta_sifen_error', 'Error al consultar estado en SIFEN', [
                    'error' => $mensajeError,
                    'fecha' => now()->format('Y-m-d H:i:s')
                ]);
                
                return back()->with('error', 'Error al consultar estado en SIFEN: ' . $mensajeError);
            }
            
            // Obtener el resultado exitoso
            $resultado = $resultado['resultado'];
            
            // Registrar la consulta como un evento
            $factura->registrarEvento('consulta_sifen', 'Consulta de estado en SIFEN', [
                'resultado' => $resultado,
                'fecha' => now()->format('Y-m-d H:i:s')
            ]);
            
            // Actualizar estado de la factura según la respuesta de SIFEN
            if (isset($resultado['respuesta']['codigo'])) {
                // Adaptar según documentación oficial de SIFEN
                switch($resultado['respuesta']['codigo']) {
                    case '0': // Aprobado
                    case '0001': // Procesado correctamente
                    case '0101': // Documento ya aprobado
                        $factura->estado = FacturaElectronica::ESTADO_ACEPTADA;
                        break;
                        
                    case '100': // Rechazado
                    case '9999': // Error de validación 
                        $factura->estado = FacturaElectronica::ESTADO_RECHAZADA;
                        $factura->observacion = $resultado['respuesta']['mensaje'] ?? 'Documento rechazado por SIFEN';
                        break;
                        
                    case '9001': // Sistema SIFEN en mantenimiento
                    case '9002': // Error de comunicación
                    case '9003': // Error de timeout
                        // Estos son errores no relacionados con el documento, así que no cambiamos el estado
                        // pero registramos la observación
                        $factura->observacion = $resultado['respuesta']['mensaje'] ?? 'Error temporal en SIFEN';
                        break;
                        
                    case '0400': // Documento cancelado
                        $factura->estado = FacturaElectronica::ESTADO_CANCELADA;
                        break;
                        
                    case '0500': // Documento anulado/inutilizado  
                        $factura->estado = FacturaElectronica::ESTADO_INUTILIZADA;
                        break;
                        
                    default:
                        // Otros códigos que no cambian el estado
                        // pero actualizamos la observación
                        $factura->observacion = 'Código SIFEN ' . $resultado['respuesta']['codigo'] . ': ' . 
                                              ($resultado['respuesta']['mensaje'] ?? 'Sin detalles');
                }
                
                $factura->save();
            }
            
            return view('facturas.verificacion', [
                'factura' => $factura,
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            Log::error('Error al verificar estado en SIFEN: ' . $e->getMessage());
            return back()->with('error', 'Error al verificar estado en SIFEN: ' . $e->getMessage());
        }
    }
      /**
     * Envía una factura electrónica a SIFEN
     *
     * @param Request $request
     * @param FacturaElectronica $factura
     * @return \Illuminate\Http\Response
     */
    public function enviarASIFEN(Request $request, FacturaElectronica $factura)
    {
        try {
            // Verificar que la factura tenga XML
            if (empty($factura->xml)) {
                return back()->with('error', 'La factura no tiene un XML para enviar a SIFEN');
            }
            
            // Verificar que la factura no esté ya enviada o aceptada
            if ($factura->estado === FacturaElectronica::ESTADO_ACEPTADA) {
                return back()->with('error', 'La factura ya fue aceptada por SIFEN, no es necesario enviarla nuevamente');
            }
            
            // Determinar si forzamos modo real o simulación
            $options = [];
            if ($request->has('modo')) {
                $options['simular'] = $request->modo === 'simular';
            }
            
            // Enviar a SIFEN
            $resultado = $this->facturacionService->enviarDocumentoSIFEN($factura->xml, $options);
            
            // Verificar si hay error en la respuesta
            if (!isset($resultado['success']) || $resultado['success'] === false) {
                $mensajeError = isset($resultado['message']) ? $resultado['message'] : 'Error desconocido al enviar documento';
                
                // Registrar el error como un evento
                $factura->registrarEvento('envio_sifen_error', 'Error al enviar a SIFEN', [
                    'error' => $mensajeError,
                    'fecha' => now()->format('Y-m-d H:i:s')
                ]);
                
                return back()->with('error', 'Error al enviar factura a SIFEN: ' . $mensajeError);
            }
            
            // Obtener el resultado exitoso
            $resultado = $resultado['resultado'];
            
            // Registrar el envío como un evento
            $factura->registrarEvento('envio_sifen', 'Envío a SIFEN', [
                'resultado' => $resultado,
                'fecha' => now()->format('Y-m-d H:i:s')
            ]);
            
            // Actualizar estado de la factura según la respuesta de SIFEN
            if (isset($resultado['recepcion']['codigo'])) {
                // Adaptar según documentación oficial de SIFEN
                switch($resultado['recepcion']['codigo']) {
                    case '0': // Aprobado
                    case '0001': // Procesado correctamente
                        $factura->estado = FacturaElectronica::ESTADO_ACEPTADA;
                        $factura->observacion = 'Documento aceptado por SIFEN';
                        break;
                        
                    case '100': // Rechazado
                    case '9999': // Error de validación
                        $factura->estado = FacturaElectronica::ESTADO_RECHAZADA;
                        $factura->observacion = $resultado['recepcion']['mensaje'] ?? 'Documento rechazado por SIFEN';
                        break;
                        
                    default:
                        // Por defecto, si no conocemos el código específico, marcamos como enviado
                        $factura->estado = FacturaElectronica::ESTADO_ENVIADA;
                        $factura->observacion = 'Código SIFEN ' . $resultado['recepcion']['codigo'] . ': ' . 
                                              ($resultado['recepcion']['mensaje'] ?? 'Sin detalles');
                }
            } else {
                // Si no hay código de respuesta, actualizamos a enviada
                $factura->estado = FacturaElectronica::ESTADO_ENVIADA;
            }
            
            $factura->save();
            
            return redirect()->route('facturas.show', $factura)
                ->with('success', 'Factura enviada a SIFEN correctamente');
        } catch (Exception $e) {
            Log::error('Error al enviar factura a SIFEN: ' . $e->getMessage());
            return back()->with('error', 'Error al enviar factura a SIFEN: ' . $e->getMessage());
        }
    }
}
