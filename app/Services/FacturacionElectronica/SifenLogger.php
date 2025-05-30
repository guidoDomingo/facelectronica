<?php

namespace App\Services\FacturacionElectronica;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Logger especializado para operaciones de SIFEN
 */
class SifenLogger
{
    /**
     * Canal de logging para SIFEN
     */
    const LOG_CHANNEL = 'sifen';
    
    /**
     * Registra una consulta a SIFEN
     *
     * @param string $cdc Código de Control del documento
     * @param array $params Parámetros de la consulta
     * @return void
     */
    public static function logConsulta(string $cdc, array $params = [])
    {
        $datos = [
            'tipo' => 'consulta',
            'cdc' => $cdc,
            'fecha' => Carbon::now()->toIso8601String(),
            'params' => $params
        ];
        
        Log::channel(self::LOG_CHANNEL)->info("Consulta SIFEN: $cdc", $datos);
    }
    
    /**
     * Registra una respuesta de consulta de SIFEN
     *
     * @param string $cdc Código de Control del documento
     * @param mixed $respuesta La respuesta recibida
     * @param bool $exito Indica si la consulta fue exitosa
     * @return void
     */
    public static function logRespuestaConsulta(string $cdc, $respuesta, bool $exito = true)
    {
        $nivel = $exito ? 'info' : 'warning';
        
        $datos = [
            'tipo' => 'respuesta_consulta',
            'cdc' => $cdc,
            'fecha' => Carbon::now()->toIso8601String(),
            'exito' => $exito,
            'respuesta' => $respuesta
        ];
        
        Log::channel(self::LOG_CHANNEL)->$nivel("Respuesta consulta SIFEN: $cdc", $datos);
    }
    
    /**
     * Registra el envío de un documento a SIFEN
     *
     * @param string $xml XML enviado
     * @param array $params Parámetros del envío
     * @return void
     */
    public static function logEnvio(string $xml, array $params = [])
    {
        // Extraer el CDC del XML si existe
        $cdc = 'Sin CDC';
        if (preg_match('/<dId>(.*?)<\/dId>/', $xml, $matches)) {
            $cdc = $matches[1];
        }
        
        $datos = [
            'tipo' => 'envio',
            'cdc' => $cdc,
            'fecha' => Carbon::now()->toIso8601String(),
            'tamano_xml' => strlen($xml),
            'params' => $params
        ];
        
        // Guardar el XML enviado para auditoría
        $filename = 'sifen/envios/' . $cdc . '_' . time() . '.xml';
        try {
            Storage::put($filename, $xml);
            $datos['xml_path'] = $filename;
        } catch (\Exception $e) {
            $datos['error_guardado'] = $e->getMessage();
        }
        
        Log::channel(self::LOG_CHANNEL)->info("Envío SIFEN: $cdc", $datos);
    }
    
    /**
     * Registra una respuesta de envío de SIFEN
     *
     * @param string $cdc Código de Control del documento
     * @param mixed $respuesta La respuesta recibida
     * @param bool $exito Indica si el envío fue exitoso
     * @return void
     */
    public static function logRespuestaEnvio($cdc, $respuesta, bool $exito = true)
    {
        $nivel = $exito ? 'info' : 'warning';
        
        $datos = [
            'tipo' => 'respuesta_envio',
            'cdc' => $cdc,
            'fecha' => Carbon::now()->toIso8601String(),
            'exito' => $exito,
            'respuesta' => $respuesta
        ];
        
        Log::channel(self::LOG_CHANNEL)->$nivel("Respuesta envío SIFEN: $cdc", $datos);
    }
    
    /**
     * Registra el envío de un evento a SIFEN
     *
     * @param string $xml XML enviado
     * @param string $tipoEvento Tipo de evento (cancelacion, inutilizacion, notificacion, etc.)
     * @param array $params Parámetros del envío
     * @return void
     */
    public static function logEnvioEvento(string $xml, string $tipoEvento, array $params = [])
    {
        // Extraer el ID del evento del XML si existe
        $idEvento = 'Sin-ID';
        if (preg_match('/<Id>(.*?)<\/Id>/', $xml, $matches)) {
            $idEvento = $matches[1];
        }
        
        $datos = [
            'tipo' => 'envio_evento',
            'tipo_evento' => $tipoEvento,
            'id_evento' => $idEvento,
            'fecha' => Carbon::now()->toIso8601String(),
            'tamano_xml' => strlen($xml),
            'params' => $params
        ];
        
        // Guardar el XML enviado para auditoría
        $filename = 'sifen/eventos/' . $tipoEvento . '_' . $idEvento . '_' . time() . '.xml';
        try {
            Storage::put($filename, $xml);
            $datos['xml_path'] = $filename;
        } catch (\Exception $e) {
            $datos['error_guardado'] = $e->getMessage();
        }
        
        Log::channel(self::LOG_CHANNEL)->info("Envío evento SIFEN: {$tipoEvento} - {$idEvento}", $datos);
    }
    
    /**
     * Registra una respuesta de envío de evento SIFEN
     *
     * @param string $idEvento ID del evento
     * @param string $tipoEvento Tipo de evento
     * @param mixed $respuesta La respuesta recibida
     * @param bool $exito Indica si el envío fue exitoso
     * @return void
     */
    public static function logRespuestaEvento(string $idEvento, string $tipoEvento, $respuesta, bool $exito = true)
    {
        $nivel = $exito ? 'info' : 'warning';
        
        $datos = [
            'tipo' => 'respuesta_evento',
            'tipo_evento' => $tipoEvento,
            'id_evento' => $idEvento,
            'fecha' => Carbon::now()->toIso8601String(),
            'exito' => $exito,
            'respuesta' => $respuesta
        ];
        
        Log::channel(self::LOG_CHANNEL)->$nivel("Respuesta evento SIFEN: {$tipoEvento} - {$idEvento}", $datos);
    }
    
    /**
     * Registra un error en operaciones con SIFEN
     *
     * @param string $mensaje Mensaje de error
     * @param \Exception|null $exception Excepción capturada
     * @param array $contexto Datos adicionales de contexto
     * @return void
     */
    public static function logError(string $mensaje, \Exception $exception = null, array $contexto = [])
    {
        $datos = [
            'tipo' => 'error',
            'fecha' => Carbon::now()->toIso8601String(),
            'contexto' => $contexto
        ];
        
        if ($exception) {
            $datos['error'] = [
                'mensaje' => $exception->getMessage(),
                'clase' => get_class($exception),
                'archivo' => $exception->getFile(),
                'linea' => $exception->getLine(),
                'traza' => $exception->getTraceAsString()
            ];
        }
        
        Log::channel(self::LOG_CHANNEL)->error($mensaje, $datos);
    }
    
    /**
     * Registra un mensaje informativo en el log
     *
     * @param string $mensaje Mensaje a registrar
     * @param array $contexto Datos adicionales de contexto
     * @return void
     */
    public static function logInfo(string $mensaje, array $contexto = [])
    {
        $datos = [
            'tipo' => 'info',
            'fecha' => Carbon::now()->toIso8601String(),
            'contexto' => $contexto
        ];
        
        Log::channel(self::LOG_CHANNEL)->info($mensaje, $datos);
    }
}
