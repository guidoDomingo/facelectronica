<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\FacturacionElectronica\FacturacionElectronicaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class FacturacionElectronicaEventosController extends Controller
{
    protected $facturacionService;
    
    public function __construct(FacturacionElectronicaService $facturacionService)
    {
        $this->facturacionService = $facturacionService;
    }
    
    /**
     * Genera un XML para evento de cancelación
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarXmlEventoCancelacion(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'id' => 'required|integer',
                'params' => 'required|array',
                'data' => 'required|array',
                'options' => 'sometimes|array',
            ]);
            
            // Obtener datos del request
            $id = $request->input('id');
            $params = $request->input('params', []);
            $data = $request->input('data', []);
            $options = $request->input('options', []);
            
            // Si no se proporcionan parámetros completos, usar los valores por defecto
            if (empty($params['ruc'])) {
                $defaultParams = config('facturacion_electronica.contribuyente');
                $params = array_merge($defaultParams, $params);
                
                // Agregar actividades económicas si no están definidas
                if (empty($params['actividadesEconomicas'])) {
                    $params['actividadesEconomicas'] = config('facturacion_electronica.actividades_economicas', []);
                }
                
                // Agregar establecimientos si no están definidos
                if (empty($params['establecimientos'])) {
                    $params['establecimientos'] = config('facturacion_electronica.establecimientos', []);
                }
            }
            
            // Generar XML
            $xml = $this->facturacionService->generateXMLEventoCancelacion($id, $params, $data, $options);
            
            // Devolver respuesta
            return response($xml, 200)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', 'attachment; filename="evento_cancelacion.xml"');
                
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaEventosController::generarXmlEventoCancelacion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar XML de evento de cancelación: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Genera un XML para evento de inutilización
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarXmlEventoInutilizacion(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'id' => 'required|integer',
                'params' => 'required|array',
                'data' => 'required|array',
                'options' => 'sometimes|array',
            ]);
            
            // Obtener datos del request
            $id = $request->input('id');
            $params = $request->input('params', []);
            $data = $request->input('data', []);
            $options = $request->input('options', []);
            
            // Si no se proporcionan parámetros completos, usar los valores por defecto
            if (empty($params['ruc'])) {
                $defaultParams = config('facturacion_electronica.contribuyente');
                $params = array_merge($defaultParams, $params);
                
                // Agregar actividades económicas si no están definidas
                if (empty($params['actividadesEconomicas'])) {
                    $params['actividadesEconomicas'] = config('facturacion_electronica.actividades_economicas', []);
                }
                
                // Agregar establecimientos si no están definidos
                if (empty($params['establecimientos'])) {
                    $params['establecimientos'] = config('facturacion_electronica.establecimientos', []);
                }
            }
            
            // Generar XML
            $xml = $this->facturacionService->generateXMLEventoInutilizacion($id, $params, $data, $options);
            
            // Devolver respuesta
            return response($xml, 200)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', 'attachment; filename="evento_inutilizacion.xml"');
                
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaEventosController::generarXmlEventoInutilizacion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar XML de evento de inutilización: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Genera un XML para evento de conformidad
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarXmlEventoConformidad(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'id' => 'required|integer',
                'params' => 'required|array',
                'data' => 'required|array',
                'options' => 'sometimes|array',
            ]);
            
            // Obtener datos del request
            $id = $request->input('id');
            $params = $request->input('params', []);
            $data = $request->input('data', []);
            $options = $request->input('options', []);
            
            // Si no se proporcionan parámetros completos, usar los valores por defecto
            if (empty($params['ruc'])) {
                $defaultParams = config('facturacion_electronica.contribuyente');
                $params = array_merge($defaultParams, $params);
                
                // Agregar actividades económicas si no están definidas
                if (empty($params['actividadesEconomicas'])) {
                    $params['actividadesEconomicas'] = config('facturacion_electronica.actividades_economicas', []);
                }
                
                // Agregar establecimientos si no están definidos
                if (empty($params['establecimientos'])) {
                    $params['establecimientos'] = config('facturacion_electronica.establecimientos', []);
                }
            }
            
            // Generar XML
            $xml = $this->facturacionService->generateXMLEventoConformidad($id, $params, $data, $options);
            
            // Devolver respuesta
            return response($xml, 200)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', 'attachment; filename="evento_conformidad.xml"');
                
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaEventosController::generarXmlEventoConformidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar XML de evento de conformidad: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Genera un XML para evento de disconformidad
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarXmlEventoDisconformidad(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'id' => 'required|integer',
                'params' => 'required|array',
                'data' => 'required|array',
                'options' => 'sometimes|array',
            ]);
            
            // Obtener datos del request
            $id = $request->input('id');
            $params = $request->input('params', []);
            $data = $request->input('data', []);
            $options = $request->input('options', []);
            
            // Si no se proporcionan parámetros completos, usar los valores por defecto
            if (empty($params['ruc'])) {
                $defaultParams = config('facturacion_electronica.contribuyente');
                $params = array_merge($defaultParams, $params);
                
                // Agregar actividades económicas si no están definidas
                if (empty($params['actividadesEconomicas'])) {
                    $params['actividadesEconomicas'] = config('facturacion_electronica.actividades_economicas', []);
                }
                
                // Agregar establecimientos si no están definidos
                if (empty($params['establecimientos'])) {
                    $params['establecimientos'] = config('facturacion_electronica.establecimientos', []);
                }
            }
            
            // Generar XML
            $xml = $this->facturacionService->generateXMLEventoDisconformidad($id, $params, $data, $options);
            
            // Devolver respuesta
            return response($xml, 200)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', 'attachment; filename="evento_disconformidad.xml"');
                
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaEventosController::generarXmlEventoDisconformidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar XML de evento de disconformidad: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Genera un XML para evento de desconocimiento
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarXmlEventoDesconocimiento(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'id' => 'required|integer',
                'params' => 'required|array',
                'data' => 'required|array',
                'options' => 'sometimes|array',
            ]);
            
            // Obtener datos del request
            $id = $request->input('id');
            $params = $request->input('params', []);
            $data = $request->input('data', []);
            $options = $request->input('options', []);
            
            // Si no se proporcionan parámetros completos, usar los valores por defecto
            if (empty($params['ruc'])) {
                $defaultParams = config('facturacion_electronica.contribuyente');
                $params = array_merge($defaultParams, $params);
                
                // Agregar actividades económicas si no están definidas
                if (empty($params['actividadesEconomicas'])) {
                    $params['actividadesEconomicas'] = config('facturacion_electronica.actividades_economicas', []);
                }
                
                // Agregar establecimientos si no están definidos
                if (empty($params['establecimientos'])) {
                    $params['establecimientos'] = config('facturacion_electronica.establecimientos', []);
                }
            }
            
            // Generar XML
            $xml = $this->facturacionService->generateXMLEventoDesconocimiento($id, $params, $data, $options);
            
            // Devolver respuesta
            return response($xml, 200)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', 'attachment; filename="evento_desconocimiento.xml"');
                
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaEventosController::generarXmlEventoDesconocimiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar XML de evento de desconocimiento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Genera un XML para evento de notificación
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarXmlEventoNotificacion(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'id' => 'required|integer',
                'params' => 'required|array',
                'data' => 'required|array',
                'options' => 'sometimes|array',
            ]);
            
            // Obtener datos del request
            $id = $request->input('id');
            $params = $request->input('params', []);
            $data = $request->input('data', []);
            $options = $request->input('options', []);
            
            // Si no se proporcionan parámetros completos, usar los valores por defecto
            if (empty($params['ruc'])) {
                $defaultParams = config('facturacion_electronica.contribuyente');
                $params = array_merge($defaultParams, $params);
                
                // Agregar actividades económicas si no están definidas
                if (empty($params['actividadesEconomicas'])) {
                    $params['actividadesEconomicas'] = config('facturacion_electronica.actividades_economicas', []);
                }
                
                // Agregar establecimientos si no están definidos
                if (empty($params['establecimientos'])) {
                    $params['establecimientos'] = config('facturacion_electronica.establecimientos', []);
                }
            }
            
            // Generar XML
            $xml = $this->facturacionService->generateXMLEventoNotificacion($id, $params, $data, $options);
            
            // Devolver respuesta
            return response($xml, 200)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', 'attachment; filename="evento_notificacion.xml"');
                
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaEventosController::generarXmlEventoNotificacion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar XML de evento de notificación: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene información de una ciudad por su ID
     *
     * @param int $ciudadId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCiudad($ciudadId)
    {
        try {
            $ciudad = $this->facturacionService->getCiudad((int)$ciudadId);
            
            return response()->json([
                'success' => true,
                'ciudad' => $ciudad
            ]);
                
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaEventosController::getCiudad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de ciudad: ' . $e->getMessage()
            ], 500);
        }
    }
}
