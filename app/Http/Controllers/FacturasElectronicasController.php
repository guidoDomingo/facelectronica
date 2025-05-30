<?php

namespace App\Http\Controllers;

use App\Models\FacturaElectronica;
use App\Services\FacturacionElectronica\FacturacionElectronicaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class FacturasElectronicasController extends Controller
{
    protected $facturacionService;
    
    public function __construct(FacturacionElectronicaService $facturacionService)
    {
        $this->facturacionService = $facturacionService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $facturas = FacturaElectronica::orderBy('created_at', 'desc')->paginate(10);
        return view('facturas.index', compact('facturas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('facturas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'tipo_documento' => 'required|integer|min:1|max:7',
                'establecimiento' => 'required|string|size:3',
                'punto' => 'required|string|size:3',
                'numero' => 'required|string|size:7',
                'ruc_receptor' => 'required|string|max:20',
                'razon_social_receptor' => 'required|string|max:255',
                'total' => 'required|numeric|min:0',
                'moneda' => 'required|string|size:3',
                'items' => 'required|array|min:1',
                'items.*.descripcion' => 'required|string|max:255',
                'items.*.cantidad' => 'required|numeric|min:0',
                'items.*.precio_unitario' => 'required|numeric|min:0',
            ]);
            
            // Iniciar transacción
            DB::beginTransaction();
            
            // Obtener los parámetros del contribuyente desde la configuración
            $params = config('facturacion_electronica.contribuyente');
            
            // Agregar actividades económicas y establecimientos
            $params['actividadesEconomicas'] = config('facturacion_electronica.actividades_economicas', []);
            $params['establecimientos'] = config('facturacion_electronica.establecimientos', []);
            
            // Generar un CDC aleatorio para prueba
            // En un entorno real, esto lo generaría la librería o el servicio de SIFEN
            $cdc = $this->generarCDCPrueba($request->tipo_documento, $request->establecimiento, $request->punto, $request->numero);
            
            // Crear la factura electrónica
            $factura = FacturaElectronica::create([
                'cdc' => $cdc,
                'tipo_documento' => $request->tipo_documento,
                'establecimiento' => $request->establecimiento,
                'punto' => $request->punto,
                'numero' => $request->numero,
                'fecha' => now(),
                'ruc_emisor' => $params['ruc'] ?? '80069563-1',
                'razon_social_emisor' => $params['razonSocial'] ?? 'Empresa de Ejemplo S.A.',
                'ruc_receptor' => $request->ruc_receptor,
                'razon_social_receptor' => $request->razon_social_receptor,
                'total' => $request->total,
                'impuesto' => $request->impuesto ?? 0,
                'moneda' => $request->moneda,
                'estado' => FacturaElectronica::ESTADO_GENERADA,
            ]);
              // Preparar datos para generar XML
            $data = [
                'tipoDocumento' => $factura->tipo_documento,
                'establecimiento' => $factura->establecimiento,
                'punto' => $factura->punto,
                'numero' => $factura->numero,
                'descripcion' => 'Factura generada desde el sistema',
                'observacion' => $request->observacion ?? '',
                'fecha' => $factura->fecha->format('Y-m-d\TH:i:s'),
                'tipoEmision' => 1,
                'tipoTransaccion' => 1,
                'tipoImpuesto' => 1,
                'moneda' => $factura->moneda,
                'condicionAnticipo' => 1,
                'condicionTipoCambio' => 1,
                'descuentoGlobal' => 0,
                'anticipoGlobal' => 0,
                'cambio' => $request->moneda === 'USD' ? 6700 : 0,
                // Datos específicos de la factura
                'factura' => [
                    'presencia' => 1 // 1: física, 2: electrónica
                ],
                // Datos de condición de operación
                'condicion' => [
                    'tipo' => 1, // 1: contado, 2: crédito
                    'entregas' => [
                        [
                            'tipo' => 1, // 1: efectivo, 2: cheque, etc.
                            'monto' => $factura->total,
                            'moneda' => $factura->moneda
                        ]
                    ]
                ],
                'cliente' => [
                    'contribuyente' => true,
                    'ruc' => $factura->ruc_receptor,
                    'razonSocial' => $factura->razon_social_receptor,
                    'nombreFantasia' => $request->nombre_fantasia_receptor ?? $factura->razon_social_receptor,
                    'tipoOperacion' => 1,
                    'direccion' => $request->direccion_receptor ?? 'Dirección no especificada',
                    'numeroCasa' => $request->numero_casa_receptor ?? '0',
                    'departamento' => $request->departamento_receptor ?? 11,
                    'departamentoDescripcion' => $request->departamento_descripcion_receptor ?? 'ALTO PARANA',
                    'distrito' => $request->distrito_receptor ?? 143,
                    'distritoDescripcion' => $request->distrito_descripcion_receptor ?? 'DOMINGO MARTINEZ DE IRALA',
                    'ciudad' => $request->ciudad_receptor ?? 3344,
                    'ciudadDescripcion' => $request->ciudad_descripcion_receptor ?? 'PASO ITA (INDIGENA)',
                    'pais' => $request->pais_receptor ?? 'PRY',
                    'paisDescripcion' => $request->pais_descripcion_receptor ?? 'Paraguay',
                    'tipoContribuyente' => $request->tipo_contribuyente_receptor ?? 1,
                    'documentoTipo' => $request->documento_tipo_receptor ?? 1,
                ],
                'items' => []
            ];
              // Agregar los items
            foreach ($request->items as $item) {
                $ivaTipo = $item['iva_tipo'] ?? 1;
                $ivaBase = 100; // Por defecto 100 para ivaTipo = 1
                
                $data['items'][] = [
                    'codigo' => $item['codigo'] ?? 'PROD' . random_int(100, 999),
                    'descripcion' => $item['descripcion'],
                    'observacion' => $item['observacion'] ?? '',
                    'unidadMedida' => $item['unidad_medida'] ?? 77,
                    'cantidad' => $item['cantidad'],
                    'precioUnitario' => $item['precio_unitario'],
                    'iva' => $item['iva'] ?? 10,
                    'ivaTipo' => $ivaTipo,
                    'ivaBase' => $ivaBase
                ];
            }
              // Generar y firmar XML
            $xml = $this->facturacionService->generateAndSignXML($params, $data);
            
            // Guardar XML en la factura
            $factura->xml = $xml;
            $factura->save();
            
            // Confirmar transacción
            DB::commit();
            
            return redirect()->route('facturas.show', $factura)
                ->with('success', 'Factura electrónica generada correctamente');
                
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            DB::rollback();
            
            Log::error('Error en FacturasElectronicasController::store: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error al generar factura electrónica: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FacturaElectronica $factura)
    {
        return view('facturas.show', compact('factura'));
    }
    
    /**
     * Download XML file for the specified resource.
     */
    public function descargarXml(FacturaElectronica $factura)
    {
        if (empty($factura->xml)) {
            return back()->with('error', 'La factura electrónica no tiene un XML generado');
        }
        
        $filename = "factura_{$factura->tipo_documento}_{$factura->establecimiento}_{$factura->punto}_{$factura->numero}.xml";
        
        return response($factura->xml, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
    
    /**
     * Cancel the specified resource.
     */
    public function cancelar(Request $request, FacturaElectronica $factura)
    {
        try {
            // Validar datos
            $request->validate([
                'motivo' => 'required|string|min:5|max:255'
            ]);
            
            // Verificar si la factura puede ser cancelada
            if ($factura->estado !== FacturaElectronica::ESTADO_ACEPTADA) {
                throw new Exception('Solo se pueden cancelar facturas en estado aceptada');
            }
            
            // Obtener los parámetros del contribuyente desde la configuración
            $params = config('facturacion_electronica.contribuyente');
            
            // Agregar actividades económicas y establecimientos
            $params['actividadesEconomicas'] = config('facturacion_electronica.actividades_economicas', []);
            $params['establecimientos'] = config('facturacion_electronica.establecimientos', []);
            
            // Datos para el evento de cancelación
            $datosEvento = [
                'cdc' => $factura->cdc,
                'motivo' => $request->motivo
            ];
            
            // Iniciar transacción
            DB::beginTransaction();
            
            // Generar XML del evento
            $xmlEvento = $this->facturacionService->generateXMLEventoCancelacion(1, $params, $datosEvento);
            
            // Registrar evento
            $evento = $factura->eventos()->create([
                'tipo' => 'cancelacion',
                'descripcion' => 'Cancelación de factura',
                'datos' => $datosEvento,
                'xml' => $xmlEvento
            ]);
            
            // Actualizar estado de la factura
            $factura->estado = FacturaElectronica::ESTADO_CANCELADA;
            $factura->observacion = $request->motivo;
            $factura->save();
            
            // Confirmar transacción
            DB::commit();
            
            return redirect()->route('facturas.show', $factura)
                ->with('success', 'Factura electrónica cancelada correctamente');
                
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            DB::rollback();
            
            Log::error('Error en FacturasElectronicasController::cancelar: ' . $e->getMessage());
            return back()
                ->with('error', 'Error al cancelar factura electrónica: ' . $e->getMessage());
        }
    }
    
    /**
     * Genera un CDC de prueba
     * En un entorno real, esto lo generaría la librería o el servicio de SIFEN
     */
    private function generarCDCPrueba($tipoDocumento, $establecimiento, $punto, $numero)
    {
        $tipoDocumentoPad = str_pad($tipoDocumento, 2, '0', STR_PAD_LEFT);
        $ruc = '80069563'; // RUC del emisor sin dígito verificador
        $dv = '1'; // Dígito verificador del RUC
        $fecha = date('Ymd'); // Fecha actual en formato YYYYMMDD
        $timestamp = substr(time(), -6); // Últimos 6 dígitos del timestamp
        $aleatorio = str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT); // Número aleatorio
        
        return "{$tipoDocumentoPad}{$ruc}{$dv}{$establecimiento}{$punto}{$numero}{$fecha}{$timestamp}{$aleatorio}";
    }
}
