<?php

namespace App\Services\FacturacionElectronica;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\FacturacionElectronica\SifenLogger;
use Exception;

class FacturacionElectronicaService
{
    protected $nodeApiUrl;
    
    public function __construct()
    {
        $this->nodeApiUrl = config('facturacion_electronica.node_api_url', 'http://localhost:3000');
    }
    
    /**
     * Genera el XML para SIFEN (Paraguay)
     *
     * @param array $params Parámetros estáticos del Contribuyente emisor
     * @param array $data Datos variables para el documento electrónico
     * @param array $options Opciones adicionales
     * @return string XML generado
     * @throws Exception
     */
    public function generateXML(array $params, array $data, array $options = [])
    {
        try {
            $response = Http::post($this->nodeApiUrl . '/generate-xml', [
                'params' => $params,
                'data' => $data,
                'options' => $options
            ]);
            
            if ($response->successful()) {
                return $response->body();
            }
            
            throw new Exception('Error al generar XML: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaService::generateXML: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera el XML para un evento de cancelación
     *
     * @param int $id ID del evento
     * @param array $params Parámetros estáticos del Contribuyente emisor
     * @param array $data Datos del evento
     * @param array $options Opciones adicionales
     * @return string XML generado
     * @throws Exception
     */
    public function generateXMLEventoCancelacion(int $id, array $params, array $data, array $options = [])
    {
        try {
            $response = Http::post($this->nodeApiUrl . '/generate-xml-evento-cancelacion', [
                'id' => $id,
                'params' => $params,
                'data' => $data,
                'options' => $options
            ]);
            
            if ($response->successful()) {
                return $response->body();
            }
            
            throw new Exception('Error al generar XML de evento de cancelación: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaService::generateXMLEventoCancelacion: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera el XML para un evento de inutilización
     *
     * @param int $id ID del evento
     * @param array $params Parámetros estáticos del Contribuyente emisor
     * @param array $data Datos del evento
     * @param array $options Opciones adicionales
     * @return string XML generado
     * @throws Exception
     */
    public function generateXMLEventoInutilizacion(int $id, array $params, array $data, array $options = [])
    {
        try {
            $response = Http::post($this->nodeApiUrl . '/generate-xml-evento-inutilizacion', [
                'id' => $id,
                'params' => $params,
                'data' => $data,
                'options' => $options
            ]);
            
            if ($response->successful()) {
                return $response->body();
            }
            
            throw new Exception('Error al generar XML de evento de inutilización: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaService::generateXMLEventoInutilizacion: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera el XML para un evento de conformidad
     *
     * @param int $id ID del evento
     * @param array $params Parámetros estáticos del Contribuyente emisor
     * @param array $data Datos del evento
     * @param array $options Opciones adicionales
     * @return string XML generado
     * @throws Exception
     */
    public function generateXMLEventoConformidad(int $id, array $params, array $data, array $options = [])
    {
        try {
            $response = Http::post($this->nodeApiUrl . '/generate-xml-evento-conformidad', [
                'id' => $id,
                'params' => $params,
                'data' => $data,
                'options' => $options
            ]);
            
            if ($response->successful()) {
                return $response->body();
            }
            
            throw new Exception('Error al generar XML de evento de conformidad: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaService::generateXMLEventoConformidad: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera el XML para un evento de disconformidad
     *
     * @param int $id ID del evento
     * @param array $params Parámetros estáticos del Contribuyente emisor
     * @param array $data Datos del evento
     * @param array $options Opciones adicionales
     * @return string XML generado
     * @throws Exception
     */
    public function generateXMLEventoDisconformidad(int $id, array $params, array $data, array $options = [])
    {
        try {
            $response = Http::post($this->nodeApiUrl . '/generate-xml-evento-disconformidad', [
                'id' => $id,
                'params' => $params,
                'data' => $data,
                'options' => $options
            ]);
            
            if ($response->successful()) {
                return $response->body();
            }
            
            throw new Exception('Error al generar XML de evento de disconformidad: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaService::generateXMLEventoDisconformidad: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera el XML para un evento de desconocimiento
     *
     * @param int $id ID del evento
     * @param array $params Parámetros estáticos del Contribuyente emisor
     * @param array $data Datos del evento
     * @param array $options Opciones adicionales
     * @return string XML generado
     * @throws Exception
     */
    public function generateXMLEventoDesconocimiento(int $id, array $params, array $data, array $options = [])
    {
        try {
            $response = Http::post($this->nodeApiUrl . '/generate-xml-evento-desconocimiento', [
                'id' => $id,
                'params' => $params,
                'data' => $data,
                'options' => $options
            ]);
            
            if ($response->successful()) {
                return $response->body();
            }
            
            throw new Exception('Error al generar XML de evento de desconocimiento: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaService::generateXMLEventoDesconocimiento: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera el XML para un evento de notificación
     *
     * @param int $id ID del evento
     * @param array $params Parámetros estáticos del Contribuyente emisor
     * @param array $data Datos del evento
     * @param array $options Opciones adicionales
     * @return string XML generado
     * @throws Exception
     */
    public function generateXMLEventoNotificacion(int $id, array $params, array $data, array $options = [])
    {
        try {
            $response = Http::post($this->nodeApiUrl . '/generate-xml-evento-notificacion', [
                'id' => $id,
                'params' => $params,
                'data' => $data,
                'options' => $options
            ]);
            
            if ($response->successful()) {
                return $response->body();
            }
            
            throw new Exception('Error al generar XML de evento de notificación: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaService::generateXMLEventoNotificacion: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtiene información de una ciudad por su ID
     *
     * @param int $ciudadId ID de la ciudad
     * @return array Información de la ciudad
     * @throws Exception
     */
    public function getCiudad(int $ciudadId)
    {
        try {
            $response = Http::get($this->nodeApiUrl . '/get-ciudad/' . $ciudadId);
            
            if ($response->successful()) {
                return $response->json('ciudad');
            }
            
            throw new Exception('Error al obtener información de ciudad: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaService::getCiudad: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera el CDC (Código de Control) para un documento electrónico
     *
     * @param array $data Datos necesarios para generar el CDC
     * @return string CDC generado
     * @throws Exception
     */
    public function generateCDC(array $data)
    {
        try {
            $response = Http::post($this->nodeApiUrl . '/generate-cdc', [
                'data' => $data
            ]);
            
            if ($response->successful()) {
                return $response->json('cdc');
            }
            
            throw new Exception('Error al generar CDC: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaService::generateCDC: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Valida los datos de entrada conforme al manual técnico de SIFEN
     *
     * @param array $data Datos a validar
     * @return bool|array True si es válido, array con errores si no es válido
     * @throws Exception
     */
    public function validateData(array $data)
    {
        try {
            $response = Http::post($this->nodeApiUrl . '/validate-data', [
                'data' => $data
            ]);
            
            if ($response->successful()) {
                return $response->json('valid') ? true : $response->json('errors');
            }
            
            throw new Exception('Error al validar datos: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error en FacturacionElectronicaService::validateData: ' . $e->getMessage());
            throw $e;
        }
    }
      /**
     * Firma un XML utilizando el certificado digital
     *
     * @param string $xml XML a firmar
     * @param array $options Opciones adicionales para la firma
     * @return string XML firmado
     * @throws Exception si hay errores en la firma
     */
    public function firmarXML(string $xml, array $options = [])
    {
        try {
            // Verificar si la firma está habilitada
            $habilitada = config('facturacion_electronica.firma_digital.habilitada', false);
            if (!$habilitada) {
                Log::info('Firma digital no habilitada. Se devuelve el XML sin firmar.');
                return $xml;
            }
            
            $rutaCertificado = $options['rutaCertificado'] ?? config('facturacion_electronica.firma_digital.ruta_certificado');
            $claveCertificado = $options['claveCertificado'] ?? config('facturacion_electronica.firma_digital.clave_certificado');
            
            // Verificar la validez del certificado
            $certificadoService = app(CertificadoDigitalService::class);
            if (!$certificadoService->verificarCertificado($rutaCertificado, $claveCertificado)) {
                throw new Exception("El certificado no es válido o ha caducado: {$rutaCertificado}");
            }
            
            // Registrar la operación en el log
            SifenLogger::logInfo('Firmando documento XML', [
                'ruta_certificado' => $rutaCertificado,
                'tamaño_xml' => strlen($xml),
                'opciones' => array_diff_key($options, ['claveCertificado' => '']) // No logear la clave
            ]);
            
            // Llamar al servicio Node.js para firmar el XML
            $response = Http::timeout(30)->post($this->nodeApiUrl . '/sign-xml', [
                'xml' => $xml,
                'certPath' => $rutaCertificado,
                'certPassword' => $claveCertificado,
                'options' => array_diff_key($options, ['rutaCertificado' => '', 'claveCertificado' => ''])
            ]);
            
            if ($response->successful()) {
                $xmlFirmado = $response->body();
                SifenLogger::logInfo('XML firmado correctamente', [
                    'tamaño_xml_firmado' => strlen($xmlFirmado)
                ]);
                return $xmlFirmado;
            }
            
            throw new Exception('Error al firmar XML: ' . $response->body());
        } catch (Exception $e) {
            SifenLogger::logError('Error al firmar XML', $e, [
                'tamaño_xml' => strlen($xml)
            ]);
            throw $e;
        }
    }
      /**
     * Genera y firma un XML para SIFEN
     *
     * @param array $params Parámetros estáticos del Contribuyente emisor
     * @param array $data Datos variables para el documento electrónico
     * @param array $options Opciones adicionales
     * @return string XML generado y firmado
     * @throws Exception
     */
    public function generateAndSignXML(array $params, array $data, array $options = [])
    {
        $xml = $this->generateXML($params, $data, $options);
        return $this->firmarXML($xml);
    }    /**
     * Consulta el estado de un documento electrónico en SIFEN por su CDC
     *
     * @param string $cdc Código de Control del documento a consultar
     * @param array $options Opciones adicionales para la consulta
     * @return array Información del estado del documento
     * @throws Exception
     */    public function consultarEstadoDocumento(string $cdc, array $options = [])
    {
        try {
            // Obtener el ambiente actual (test o prod)
            $ambiente = config('facturacion_electronica.ambiente', 'test');
            
            // Forzar la no simulación para usar siempre la API real de SIFEN
            $options['simular'] = false;
            
            // Configurar opciones adicionales
            $requestOptions = [
                'cdc' => $cdc,
                'ambiente' => $ambiente,
                'options' => $options
            ];
            
            // Registrar la consulta en el log especializado
            SifenLogger::logConsulta($cdc, [
                'ambiente' => $ambiente,
                'options' => $options
            ]);
            
            // Llamar al servicio Node.js para consultar el estado del documento
            $response = Http::post($this->nodeApiUrl . '/consultar-estado-documento', $requestOptions);
            $responseData = $response->json();
            
            // Registrar la respuesta recibida
            $exitoso = $response->successful() && isset($responseData['success']) && $responseData['success'];
            SifenLogger::logRespuestaConsulta($cdc, $responseData, $exitoso);
            
            if ($exitoso) {
                return $responseData;
            }
            
            // Manejar el error de forma estructurada
            $mensajeError = $responseData['message'] ?? $response->body();
            throw new Exception('Error al consultar estado del documento: ' . $mensajeError);
        } catch (Exception $e) {
            // Registrar el error en el log especializado
            SifenLogger::logError('Error en consulta de estado SIFEN', $e, [
                'cdc' => $cdc,
                'ambiente' => $ambiente,
                'options' => $options
            ]);
            throw $e;
        }
    }    /**
     * Envía un documento electrónico a SIFEN
     *
     * @param string $xml XML firmado a enviar
     * @param array $options Opciones adicionales
     * @return array Respuesta de SIFEN
     * @throws Exception
     */    public function enviarDocumentoSIFEN(string $xml, array $options = [])
    {
        try {
            // Obtener el ambiente actual (test o prod)
            $ambiente = config('facturacion_electronica.ambiente', 'test');
            
            // Forzar la no simulación para usar siempre la API real de SIFEN
            $options['simular'] = false;
            
            // Configurar opciones para la petición
            $requestData = [
                'xml' => $xml,
                'ambiente' => $ambiente,
                'options' => $options
            ];
            
            // Registrar el envío en el log especializado
            SifenLogger::logEnvio($xml, [
                'ambiente' => $ambiente,
                'options' => $options
            ]);
            
            // Extraer el CDC del XML si existe
            $cdc = 'CDC-No-Encontrado';
            if (preg_match('/<dId>(.*?)<\/dId>/', $xml, $matches)) {
                $cdc = $matches[1];
            }
            
            // Llamar al servicio Node.js para enviar el documento
            $response = Http::post($this->nodeApiUrl . '/enviar-documento', $requestData);
            $responseData = $response->json();
            
            // Registrar la respuesta recibida
            $exitoso = $response->successful() && isset($responseData['success']) && $responseData['success'];
            SifenLogger::logRespuestaEnvio($cdc, $responseData, $exitoso);
            
            if ($exitoso) {
                return $responseData;
            }
            
            // Manejar el error de forma estructurada
            $mensajeError = $responseData['message'] ?? $response->body();
            throw new Exception('Error al enviar documento a SIFEN: ' . $mensajeError);
        } catch (Exception $e) {
            // Registrar el error en el log especializado
            SifenLogger::logError('Error en envío a SIFEN', $e, [
                'tamaño_xml' => strlen($xml),
                'ambiente' => $ambiente,
                'options' => $options
            ]);
            throw $e;
        }
    }
        /**
     * Envía un evento de inutilización a SIFEN
     *
     * @param string $xml XML del evento de inutilización firmado
     * @param array $options Opciones adicionales
     * @return array Respuesta de SIFEN
     * @throws Exception
     */    public function enviarEventoInutilizacion(string $xml, array $options = [])
    {
        try {
            // Obtener el ambiente actual (test o prod)
            $ambiente = config('facturacion_electronica.ambiente', 'test');
            
            // Forzar la no simulación para usar siempre la API real de SIFEN
            $options['simular'] = false;
            
            // Configurar opciones para la petición
            $requestData = [
                'xml' => $xml,
                'ambiente' => $ambiente,
                'options' => $options
            ];
            
            // Extraer el ID del evento del XML si existe
            $idEvento = 'ID-No-Encontrado';
            if (preg_match('/<Id>(.*?)<\/Id>/', $xml, $matches)) {
                $idEvento = $matches[1];
            }
            
            // Registrar el envío en el log especializado
            SifenLogger::logEnvioEvento($xml, 'inutilizacion', [
                'ambiente' => $ambiente,
                'options' => $options,
                'id_evento' => $idEvento
            ]);
            
            // Llamar al servicio Node.js para enviar el evento
            $response = Http::post($this->nodeApiUrl . '/enviar-evento-inutilizacion', $requestData);
            $responseData = $response->json();
            
            // Registrar la respuesta recibida
            $exitoso = $response->successful() && isset($responseData['success']) && $responseData['success'];
            SifenLogger::logRespuestaEvento($idEvento, 'inutilizacion', $responseData, $exitoso);
            
            if ($exitoso) {
                return $responseData;
            }
            
            // Manejar el error de forma estructurada
            $mensajeError = $responseData['message'] ?? $response->body();
            throw new Exception('Error al enviar evento de inutilización a SIFEN: ' . $mensajeError);
        } catch (Exception $e) {
            // Registrar el error en el log especializado
            SifenLogger::logError('Error en envío de evento de inutilización a SIFEN', $e, [
                'tamaño_xml' => strlen($xml),
                'ambiente' => $ambiente ?? 'desconocido',
                'options' => $options
            ]);
            throw $e;
        }
    }
    
    /**
     * Envía un evento de notificación a SIFEN
     *
     * @param string $xml XML del evento de notificación firmado
     * @param array $options Opciones adicionales
     * @return array Respuesta de SIFEN
     * @throws Exception
     */    public function enviarEventoNotificacion(string $xml, array $options = [])
    {
        try {
            // Obtener el ambiente actual (test o prod)
            $ambiente = config('facturacion_electronica.ambiente', 'test');
            
            // Forzar la no simulación para usar siempre la API real de SIFEN
            $options['simular'] = false;
            
            // Configurar opciones para la petición
            $requestData = [
                'xml' => $xml,
                'ambiente' => $ambiente,
                'options' => $options
            ];
            
            // Extraer el ID del evento del XML si existe
            $idEvento = 'ID-No-Encontrado';
            if (preg_match('/<Id>(.*?)<\/Id>/', $xml, $matches)) {
                $idEvento = $matches[1];
            }
            
            // Registrar el envío en el log especializado
            SifenLogger::logEnvioEvento($xml, 'notificacion', [
                'ambiente' => $ambiente,
                'options' => $options,
                'id_evento' => $idEvento
            ]);
            
            // Llamar al servicio Node.js para enviar el evento
            $response = Http::post($this->nodeApiUrl . '/enviar-evento-notificacion', $requestData);
            $responseData = $response->json();
            
            // Registrar la respuesta recibida
            $exitoso = $response->successful() && isset($responseData['success']) && $responseData['success'];
            SifenLogger::logRespuestaEvento($idEvento, 'notificacion', $responseData, $exitoso);
            
            if ($exitoso) {
                return $responseData;
            }
            
            // Manejar el error de forma estructurada
            $mensajeError = $responseData['message'] ?? $response->body();
            throw new Exception('Error al enviar evento de notificación a SIFEN: ' . $mensajeError);
        } catch (Exception $e) {
            // Registrar el error en el log especializado
            SifenLogger::logError('Error en envío de evento de notificación a SIFEN', $e, [
                'tamaño_xml' => strlen($xml),
                'ambiente' => $ambiente ?? 'desconocido',
                'options' => $options
            ]);
            throw $e;
        }
    }
}