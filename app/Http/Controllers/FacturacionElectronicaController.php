<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\FacturacionElectronica\FacturacionElectronicaServiceV2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class FacturacionElectronicaController extends Controller
{
    protected $facturacionService;
    
    public function __construct(FacturacionElectronicaServiceV2 $facturacionService)
    {
        $this->facturacionService = $facturacionService;
    }
    
    /**
     * Genera un XML para facturación electrónica
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarXML(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'params' => 'required|array',
                'data' => 'required|array',
                'options' => 'sometimes|array',
            ]);
            
            // Obtener datos del request
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
            $xml = $this->facturacionService->generateXML($params, $data, $options);
            
            // Devolver respuesta
            return response($xml, 200)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', 'attachment; filename="documento_electronico.xml"');
                
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaController::generarXML: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar XML: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Genera un CDC (Código de Control) para un documento electrónico
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generarCDC(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'data' => 'required|array',
            ]);
            
            // Obtener datos del request
            $data = $request->input('data', []);
            
            // Generar CDC
            $cdc = $this->facturacionService->generateCDC($data);
            
            // Devolver respuesta
            return response()->json([
                'success' => true,
                'cdc' => $cdc
            ]);
                
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaController::generarCDC: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar CDC: ' . $e->getMessage()
            ], 500);
        }
    }
      /**
     * Valida los datos de entrada conforme al manual técnico de SIFEN
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validarDatos(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'data' => 'required|array',
            ]);
            
            // Obtener datos del request
            $data = $request->input('data', []);
            
            // Validar datos
            $result = $this->facturacionService->validateData($data);
            
            // Si el resultado es true, los datos son válidos
            if ($result === true) {
                return response()->json([
                    'success' => true,
                    'valid' => true,
                    'message' => 'Los datos son válidos'
                ]);
            }
            
            // Si no, devolver los errores
            return response()->json([
                'success' => true,
                'valid' => false,
                'errors' => $result
            ]);
                
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaController::validarDatos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al validar datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica si un documento electrónico está generado correctamente en SIFEN
     * mediante la consulta a la API real de SIFEN
     *
     * @param Request $request
     * @param string|null $cdc
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarDocumentoSifen(Request $request, $cdc = null)
    {
        try {
            // Si no se proporciona CDC en la URL, lo buscamos en el request
            if (!$cdc) {
                $request->validate([
                    'cdc' => 'required|string|size:44',
                ]);
                $cdc = $request->input('cdc');
            }            // Opciones adicionales
            $options = $request->input('options', []);
            
            // Forzamos la no simulación para asegurar que se realice la consulta real a SIFEN
            $options = ['simular' => false];
            
            // Consultar el estado del documento en SIFEN
            $resultado = $this->facturacionService->consultarEstadoDocumento($cdc, $options);
            
            // Preparar la respuesta
            $response = [
                'success' => true,
                'cdc' => $cdc,
                'resultado' => $resultado,
                'generado_en_sifen' => false,
                'mensaje' => 'El documento no se encuentra en SIFEN'
            ];
            
            // Verificar si el documento está en SIFEN basado en la respuesta
            if (isset($resultado['estado']) && $resultado['estado'] === 'real') {
                $codigoRespuesta = $resultado['respuesta']['codigo'] ?? '999';
                
                // Códigos que indican que el documento existe en SIFEN
                if (in_array($codigoRespuesta, ['0', '100', '101', '104'])) {
                    $response['generado_en_sifen'] = true;
                    $response['mensaje'] = 'El documento se encuentra registrado en SIFEN';
                    $response['estado_sifen'] = $resultado['respuesta']['estado'] ?? 'Desconocido';
                }
            }
            
            return response()->json($response);
                
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaController::verificarDocumentoSifen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar documento en SIFEN: ' . $e->getMessage()
            ], 500);
        }
    }
}