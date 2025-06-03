<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\FacturacionElectronica\FacturacionElectronicaServiceV2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class EjemploFacturacionController extends Controller
{
    protected $facturacionService;
    
    public function __construct(FacturacionElectronicaServiceV2 $facturacionService)
    {
        $this->facturacionService = $facturacionService;
    }
    
    /**
     * Muestra un formulario para generar una factura electrónica
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('facturacion.index');
    }
    
    /**
     * Genera una factura electrónica de ejemplo
     *
     * @return \Illuminate\Http\Response
     */
    public function generarEjemplo()
    {
        try {
            // Obtener los parámetros del contribuyente desde la configuración
            $params = config('facturacion_electronica.contribuyente');
            
            // Agregar actividades económicas y establecimientos
            $params['actividadesEconomicas'] = [
                [
                    'codigo' => '1254',
                    'descripcion' => 'Desarrollo de Software',
                ]
            ];
            
            $params['establecimientos'] = [
                [
                    'codigo' => '001',
                    'direccion' => 'Barrio Carolina',
                    'numeroCasa' => '0',
                    'complementoDireccion1' => 'Entre calle 2',
                    'complementoDireccion2' => 'y Calle 7',
                    'departamento' => 11,
                    'departamentoDescripcion' => 'ALTO PARANA',
                    'distrito' => 145,
                    'distritoDescripcion' => 'CIUDAD DEL ESTE',
                    'ciudad' => 3432,
                    'ciudadDescripcion' => 'PUERTO PTE.STROESSNER (MUNIC)',
                    'telefono' => '0973-527155',
                    'email' => 'empresa@empresa.com.py',
                    'denominacion' => 'Sucursal 1',
                ]
            ];
              // Datos de ejemplo para la factura
            $data = [
                'tipoDocumento' => 1,
                'establecimiento' => '001',
                'codigoSeguridadAleatorio' => '298398',
                'punto' => '001',
                'numero' => '0000001',
                'descripcion' => 'Factura de ejemplo',
                'observacion' => 'Esta es una factura de ejemplo generada desde Laravel',
                'fecha' => date('Y-m-d\TH:i:s'),
                'tipoEmision' => 1,
                'tipoTransaccion' => 1,
                'tipoImpuesto' => 1,
                'moneda' => 'PYG',
                'condicionAnticipo' => 1,
                'condicionTipoCambio' => 1,
                'descuentoGlobal' => 0,
                'anticipoGlobal' => 0,
                'cambio' => 6700,
                
                // Datos específicos de la factura (según el error)
                'factura' => [
                    'presencia' => 1 // 1: física, 2: electrónica
                ],
                  // Datos de condición de operación (según el error)
                'condicion' => [
                    'tipo' => 1, // 1: contado, 2: crédito
                    'entregas' => [
                        [
                            'tipo' => 1, // 1: efectivo, 2: cheque, etc.
                            'monto' => 250000, // monto de la entrega
                            'moneda' => 'PYG'
                        ]
                    ]
                ],
                
                'cliente' => [
                    'contribuyente' => true,
                    'ruc' => '2005001-1',
                    'razonSocial' => 'Cliente Ejemplo',
                    'nombreFantasia' => 'Cliente Ejemplo',
                    'tipoOperacion' => 1,
                    'direccion' => 'Avda Calle Segunda y Proyectada',
                    'numeroCasa' => '1515',
                    'departamento' => 11,
                    'departamentoDescripcion' => 'ALTO PARANA',
                    'distrito' => 143,
                    'distritoDescripcion' => 'DOMINGO MARTINEZ DE IRALA',
                    'ciudad' => 3344,
                    'ciudadDescripcion' => 'PASO ITA (INDIGENA)',
                    'pais' => 'PRY',
                    'paisDescripcion' => 'Paraguay',
                    'tipoContribuyente' => 1,
                    'documentoTipo' => 1,
                    'documentoNumero' => '2324234',
                    'telefono' => '061-575903',
                    'celular' => '0973-809103',
                    'email' => 'cliente@empresa.com',
                    'codigo' => '1548'
                ],
                'items' => [
                    [
                        'codigo' => 'PROD001',
                        'descripcion' => 'Producto de ejemplo 1',
                        'observacion' => 'Observación del producto',
                        'unidadMedida' => 77,
                        'cantidad' => 1,
                        'precioUnitario' => 150000,
                        'iva' => 10,
                        'ivaTipo' => 1,
                        'ivaBase' => 100 // Según el error, para ivaTipo = 1 debe ser 100
                    ],
                    [
                        'codigo' => 'PROD002',
                        'descripcion' => 'Producto de ejemplo 2',
                        'observacion' => '',
                        'unidadMedida' => 77,
                        'cantidad' => 2,
                        'precioUnitario' => 50000,
                        'iva' => 10,
                        'ivaTipo' => 1,
                        'ivaBase' => 100 // Según el error, para ivaTipo = 1 debe ser 100
                    ]
                ]
            ];
            
            // Generar XML
            $xml = $this->facturacionService->generateXML($params, $data);
            
            // Devolver el XML como respuesta
            return response($xml, 200)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', 'attachment; filename="factura_ejemplo.xml"');
                
        } catch (Exception $e) {
            Log::error('Error en EjemploFacturacionController::generarEjemplo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar factura de ejemplo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Genera un CDC de ejemplo
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generarCDCEjemplo()
    {
        try {
            // Datos de ejemplo para generar CDC
            $data = [
                'tipoDocumento' => 1,
                'establecimiento' => '001',
                'punto' => '001',
                'numero' => '0000001'
            ];
            
            // Generar CDC
            $cdc = $this->facturacionService->generateCDC($data);
            
            // Devolver respuesta
            return response()->json([
                'success' => true,
                'cdc' => $cdc,
                'message' => 'CDC generado correctamente'
            ]);
                
        } catch (Exception $e) {
            Log::error('Error en EjemploFacturacionController::generarCDCEjemplo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar CDC de ejemplo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Muestra la vista de eventos de facturación electrónica
     *
     * @return \Illuminate\Http\Response
     */
    public function eventos()
    {
        return view('facturacion.eventos');
    }
}