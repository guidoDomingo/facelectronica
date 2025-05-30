<?php

namespace App\Http\Controllers;

use App\Models\FacturaElectronica;
use App\Models\FacturaElectronicaEvento;
use App\Services\FacturacionElectronica\FacturacionElectronicaService;
use App\Services\FacturacionElectronica\CertificadoDigitalService;
use App\Services\FacturacionElectronica\SifenLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class SifenDashboardController extends Controller
{
    protected $facturacionService;
    protected $certificadoService;
    
    public function __construct(
        FacturacionElectronicaService $facturacionService,
        CertificadoDigitalService $certificadoService
    ) {
        $this->facturacionService = $facturacionService;
        $this->certificadoService = $certificadoService;
        
        // Asegurar que sólo usuarios autenticados accedan
        $this->middleware('auth');
    }
    
    /**
     * Muestra el dashboard principal de monitoreo SIFEN
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Estadísticas generales
        $estadisticas = [
            'total' => FacturaElectronica::count(),
            'aceptadas' => FacturaElectronica::where('estado', FacturaElectronica::ESTADO_ACEPTADA)->count(),
            'rechazadas' => FacturaElectronica::where('estado', FacturaElectronica::ESTADO_RECHAZADA)->count(),
            'pendientes' => FacturaElectronica::whereIn('estado', [
                FacturaElectronica::ESTADO_GENERADA, 
                FacturaElectronica::ESTADO_ENVIADA
            ])->count(),
            'canceladas' => FacturaElectronica::where('estado', FacturaElectronica::ESTADO_CANCELADA)->count(),
            'inutilizadas' => FacturaElectronica::where('estado', FacturaElectronica::ESTADO_INUTILIZADA)->count(),
        ];
        
        // Facturas recientes
        $facturasRecientes = FacturaElectronica::orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        // Eventos recientes
        $eventosRecientes = FacturaElectronicaEvento::with('facturaElectronica')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
        
        // Verificar estado del certificado
        $certificadoInfo = $this->certificadoService->obtenerInfoCertificado();
        
        // Estado del ambiente
        $ambiente = config('facturacion_electronica.ambiente', 'test');
        $modoDesarrollo = app()->environment() !== 'production';
        
        return view('admin.sifen-dashboard', compact(
            'estadisticas',
            'facturasRecientes',
            'eventosRecientes',
            'certificadoInfo',
            'ambiente',
            'modoDesarrollo'
        ));
    }
    
    /**
     * Muestra los documentos que necesitan reenvío o verificación
     *
     * @return \Illuminate\View\View
     */
    public function pendientes()
    {
        $facturasPendientes = FacturaElectronica::whereIn('estado', [
                FacturaElectronica::ESTADO_GENERADA, 
                FacturaElectronica::ESTADO_ENVIADA
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Estadísticas de pendientes
        $estadisticas = [
            'pendientesHoy' => FacturaElectronica::whereIn('estado', [
                    FacturaElectronica::ESTADO_GENERADA, 
                    FacturaElectronica::ESTADO_ENVIADA
                ])
                ->whereDate('created_at', Carbon::today())
                ->count(),
            'pendientesSemana' => FacturaElectronica::whereIn('estado', [
                    FacturaElectronica::ESTADO_GENERADA, 
                    FacturaElectronica::ESTADO_ENVIADA
                ])
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->count(),
        ];
        
        return view('admin.sifen-pendientes', compact(
            'facturasPendientes',
            'estadisticas'
        ));
    }
    
    /**
     * Muestra los errores y rechazos recientes
     *
     * @return \Illuminate\View\View
     */
    public function errores()
    {
        // Facturas rechazadas
        $facturasRechazadas = FacturaElectronica::where('estado', FacturaElectronica::ESTADO_RECHAZADA)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
        
        // Eventos de error
        $eventosError = FacturaElectronicaEvento::with('facturaElectronica')
            ->where('tipo', 'like', '%error%')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.sifen-errores', compact(
            'facturasRechazadas',
            'eventosError'
        ));
    }
    
    /**
     * Reintentar el envío de una factura rechazada o pendiente
     *
     * @param Request $request
     * @param FacturaElectronica $factura
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reintentarEnvio(Request $request, FacturaElectronica $factura)
    {
        try {
            // Verificar que la factura está en un estado que permite reintento
            if (!in_array($factura->estado, [
                FacturaElectronica::ESTADO_GENERADA, 
                FacturaElectronica::ESTADO_ENVIADA,
                FacturaElectronica::ESTADO_RECHAZADA
            ])) {
                return back()->with('error', 'Esta factura no está en un estado que permita reintento');
            }
            
            // Verificar que tenga XML
            if (empty($factura->xml)) {
                return back()->with('error', 'La factura no tiene XML para enviar');
            }
            
            // Enviar a SIFEN
            $resultado = $this->facturacionService->enviarDocumentoSIFEN($factura->xml);
            
            // Verificar resultado
            if (!isset($resultado['success']) || $resultado['success'] === false) {
                return back()->with('error', 'Error al reenviar factura: ' . 
                    ($resultado['message'] ?? 'Error desconocido'));
            }
            
            // Registrar éxito y actualizar estado
            $factura->registrarEvento('reenvio_sifen', 'Reenvío manual a SIFEN', [
                'resultado' => $resultado['resultado'],
                'usuario' => auth()->user()->name
            ]);
            
            // Actualizar estado según la respuesta
            if (isset($resultado['resultado']['recepcion']['codigo']) && 
                ($resultado['resultado']['recepcion']['codigo'] === '0' || 
                 $resultado['resultado']['recepcion']['codigo'] === '0001')) {
                $factura->estado = FacturaElectronica::ESTADO_ACEPTADA;
                $factura->observacion = 'Documento aceptado por SIFEN en reenvío manual';
                $factura->save();
                
                return back()->with('success', 'Factura reenviada y aceptada correctamente');
            } else {
                $mensaje = $resultado['resultado']['recepcion']['mensaje'] ?? 'Sin detalles adicionales';
                $factura->observacion = 'Respuesta de reenvío: ' . $mensaje;
                $factura->save();
                
                return back()->with('warning', 'Factura reenviada. Verifique el estado: ' . $mensaje);
            }
        } catch (Exception $e) {
            SifenLogger::logError('Error al reintentar envío desde dashboard', $e, [
                'factura_id' => $factura->id,
                'cdc' => $factura->cdc
            ]);
            
            return back()->with('error', 'Error al reintentar envío: ' . $e->getMessage());
        }
    }
    
    /**
     * Verificar el estado actual de una factura en SIFEN
     *
     * @param Request $request
     * @param FacturaElectronica $factura
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verificarEstado(Request $request, FacturaElectronica $factura)
    {
        try {
            // Verificar que la factura tenga CDC
            if (empty($factura->cdc)) {
                return back()->with('error', 'La factura no tiene CDC para consultar');
            }
            
            // Consultar estado en SIFEN
            $resultado = $this->facturacionService->consultarEstadoDocumento($factura->cdc);
            
            // Verificar resultado
            if (!isset($resultado['success']) || $resultado['success'] === false) {
                return back()->with('error', 'Error al consultar estado: ' . 
                    ($resultado['message'] ?? 'Error desconocido'));
            }
            
            // Registrar consulta
            $factura->registrarEvento('consulta_dashboard', 'Consulta manual desde dashboard', [
                'resultado' => $resultado['resultado'],
                'usuario' => auth()->user()->name
            ]);
            
            // Actualizar estado según la respuesta
            $mensaje = $this->actualizarEstadoSegunRespuesta($factura, $resultado['resultado']);
            
            return back()->with('info', 'Estado verificado: ' . $mensaje);
        } catch (Exception $e) {
            SifenLogger::logError('Error al verificar estado desde dashboard', $e, [
                'factura_id' => $factura->id,
                'cdc' => $factura->cdc
            ]);
            
            return back()->with('error', 'Error al verificar estado: ' . $e->getMessage());
        }
    }
    
    /**
     * Verificar el estado del certificado digital
     *
     * @return \Illuminate\View\View
     */
    public function certificado()
    {
        $certificadoInfo = $this->certificadoService->obtenerInfoCertificado();
        $habilitado = config('facturacion_electronica.firma_digital.habilitada', false);
        $rutaCertificado = config('facturacion_electronica.firma_digital.ruta_certificado', '');
        
        return view('admin.sifen-certificado', compact(
            'certificadoInfo',
            'habilitado',
            'rutaCertificado'
        ));
    }
    
    /**
     * Importa un nuevo certificado digital
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importarCertificado(Request $request)
    {
        $request->validate([
            'certificado' => 'required|file|max:2048',
            'clave' => 'required|string',
        ]);
        
        try {
            $archivo = $request->file('certificado');
            $rutaTemporal = $archivo->getRealPath();
            
            $resultado = $this->certificadoService->importarCertificado(
                $rutaTemporal, 
                $request->clave,
                $archivo->getClientOriginalName()
            );
            
            if ($resultado) {
                return back()->with('success', 'Certificado importado correctamente');
            } else {
                return back()->with('error', 'Error al importar el certificado');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Error al importar certificado: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualiza el estado de una factura según la respuesta de SIFEN
     *
     * @param FacturaElectronica $factura
     * @param array $resultado
     * @return string Mensaje descriptivo del resultado
     */
    private function actualizarEstadoSegunRespuesta(FacturaElectronica $factura, array $resultado): string
    {
        if (isset($resultado['respuesta']['codigo'])) {
            $codigo = $resultado['respuesta']['codigo'];
            $mensaje = $resultado['respuesta']['mensaje'] ?? 'Sin detalles adicionales';
            
            // Adaptar según documentación oficial de SIFEN
            switch($codigo) {
                case '0': // Aprobado
                case '0001': // Procesado correctamente
                case '0101': // Documento ya aprobado
                    $factura->estado = FacturaElectronica::ESTADO_ACEPTADA;
                    $factura->observacion = 'Documento aceptado por SIFEN';
                    $factura->save();
                    return "Documento aceptado ({$codigo}): {$mensaje}";
                    
                case '100': // Rechazado
                case '9999': // Error de validación 
                    $factura->estado = FacturaElectronica::ESTADO_RECHAZADA;
                    $factura->observacion = "Documento rechazado: {$mensaje}";
                    $factura->save();
                    return "Documento rechazado ({$codigo}): {$mensaje}";
                    
                case '0400': // Documento cancelado
                    $factura->estado = FacturaElectronica::ESTADO_CANCELADA;
                    $factura->observacion = "Documento cancelado en SIFEN: {$mensaje}";
                    $factura->save();
                    return "Documento cancelado ({$codigo}): {$mensaje}";
                    
                case '0500': // Documento anulado/inutilizado  
                    $factura->estado = FacturaElectronica::ESTADO_INUTILIZADA;
                    $factura->observacion = "Documento inutilizado en SIFEN: {$mensaje}";
                    $factura->save();
                    return "Documento inutilizado ({$codigo}): {$mensaje}";
                    
                default:
                    // Otros códigos que no cambian el estado
                    $factura->observacion = "Código SIFEN {$codigo}: {$mensaje}";
                    $factura->save();
                    return "Código no reconocido ({$codigo}): {$mensaje}";
            }
        }
        
        return "No se pudo interpretar la respuesta de SIFEN";
    }
}
