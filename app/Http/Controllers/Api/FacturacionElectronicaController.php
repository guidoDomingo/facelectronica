<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FacturacionElectronica\FacturacionElectronicaServiceV2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Controlador para la API de Facturación Electrónica
 * 
 * Este controlador expone endpoints para generar documentos XML para SIFEN
 * utilizando la implementación nativa en PHP.
 */
class FacturacionElectronicaController extends Controller
{
    /**
     * Servicio de facturación electrónica
     * 
     * @var FacturacionElectronicaServiceV2
     */
    protected $facturacionService;
    
    /**
     * Constructor
     * 
     * @param FacturacionElectronicaServiceV2 $facturacionService
     */
    public function __construct(FacturacionElectronicaServiceV2 $facturacionService)
    {
        $this->facturacionService = $facturacionService;
    }
    
    /**
     * Genera un XML para SIFEN
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarXml(Request $request)
    {
        try {
            // Validar la solicitud
            $request->validate([
                'params' => 'required|array',
                'data' => 'required|array',
                'options' => 'nullable|array'
            ]);
            
            // Obtener parámetros de la solicitud
            $params = $request->input('params');
            $data = $request->input('data');
            $options = $request->input('options', []);
            
            // Generar XML
            $xml = $this->facturacionService->generateXML($params, $data, $options);
            
            // Devolver respuesta con el XML
            return response($xml, 200)
                ->header('Content-Type', 'application/xml');
        } catch (Exception $e) {
            Log::error('Error al generar XML: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Genera un CDC (Código de Control)
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarCdc(Request $request)
    {
        try {
            // Validar la solicitud
            $request->validate([
                'data' => 'required|array'
            ]);
            
            // Obtener datos de la solicitud
            $data = $request->input('data');
            
            // Obtener parámetros del contribuyente desde la configuración
            $params = config('facturacion_electronica.contribuyente');
            
            // Generar CDC
            $cdc = $this->facturacionService->generateCDC($params, $data);
            
            // Devolver respuesta con el CDC
            return response()->json([
                'success' => true,
                'cdc' => $cdc
            ]);
        } catch (Exception $e) {
            Log::error('Error al generar CDC: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Valida datos según el manual técnico de SIFEN
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function validarDatos(Request $request)
    {
        try {
            // Validar la solicitud
            $request->validate([
                'data' => 'required|array'
            ]);
            
            // Obtener datos de la solicitud
            $data = $request->input('data');
            
            // Obtener parámetros del contribuyente desde la configuración
            $params = config('facturacion_electronica.contribuyente');
            
            // Validar datos
            $validationResult = $this->facturacionService->validateData($params, $data);
            
            // Devolver resultado de la validación
            return response()->json([
                'success' => $validationResult['success'],
                'errors' => $validationResult['errors']
            ]);
        } catch (Exception $e) {
            Log::error('Error al validar datos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Genera un XML para un evento de cancelación
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarXmlEventoCancelacion(Request $request)
    {
        try {
            // Validar la solicitud
            $request->validate([
                'id' => 'required|integer',
                'params' => 'required|array',
                'data' => 'required|array',
                'options' => 'nullable|array'
            ]);
            
            // Obtener parámetros de la solicitud
            $id = $request->input('id');
            $params = $request->input('params');
            $data = $request->input('data');
            $options = $request->input('options', []);
            
            // Generar XML
            $xml = $this->facturacionService->generateXMLEventoCancelacion($id, $params, $data, $options);
            
            // Devolver respuesta con el XML
            return response($xml, 200)
                ->header('Content-Type', 'application/xml');
        } catch (Exception $e) {
            Log::error('Error al generar XML de evento de cancelación: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Genera un XML para un evento de inutilización
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarXmlEventoInutilizacion(Request $request)
    {
        try {
            // Validar la solicitud
            $request->validate([
                'id' => 'required|integer',
                'params' => 'required|array',
                'data' => 'required|array',
                'options' => 'nullable|array'
            ]);
            
            // Obtener parámetros de la solicitud
            $id = $request->input('id');
            $params = $request->input('params');
            $data = $request->input('data');
            $options = $request->input('options', []);
            
            // Generar XML
            $xml = $this->facturacionService->generateXMLEventoInutilizacion($id, $params, $data, $options);
            
            // Devolver respuesta con el XML
            return response($xml, 200)
                ->header('Content-Type', 'application/xml');
        } catch (Exception $e) {
            Log::error('Error al generar XML de evento de inutilización: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}