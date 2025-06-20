<?php

namespace App\Services\FacturacionElectronica;

use App\Services\FacturacionElectronica\XmlGenerator\XmlGeneratorService;
use App\Services\FacturacionElectronica\XmlGenerator\Helpers\CdcGenerator;
use App\Services\FacturacionElectronica\SifenLogger;
use App\Services\SifenClientV3;
use Exception;

/**
 * Servicio principal para la facturación electrónica de Paraguay (SIFEN)
 * 
 * Esta versión implementa la generación de XML directamente en PHP
 * sin depender del servicio Node.js.
 */
class FacturacionElectronicaServiceV2
{
    /**
     * Servicio generador de XML
     * 
     * @var XmlGeneratorService
     */
    protected $xmlGenerator;
    
    /**
     * Generador de CDC
     * 
     * @var CdcGenerator
     */
    protected $cdcGenerator;
    
    /**
     * Cliente Sifen
     * 
     * @var SifenClientV3
     */
    protected $sifenClient;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->xmlGenerator = new XmlGeneratorService();
        $this->cdcGenerator = new CdcGenerator();
        $this->sifenClient = new SifenClientV3();
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
    public function generateXML(array $params, array $data, array $options = []): string
    {
        try {
            SifenLogger::logInfo('Generando XML para documento electrónico', [
                'tipo_documento' => $data['tipoDocumento'] ?? 'No especificado',
                'establecimiento' => $data['establecimiento'] ?? 'No especificado',
                'punto' => $data['punto'] ?? 'No especificado',
                'numero' => $data['numero'] ?? 'No especificado'
            ]);
            
            return $this->xmlGenerator->generateXMLDE($params, $data, $options);
        } catch (Exception $e) {
            SifenLogger::logError('Error en FacturacionElectronicaServiceV2::generateXML: ' . $e->getMessage());
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
    public function generateXMLEventoCancelacion(int $id, array $params, array $data, array $options = []): string
    {
        try {
            SifenLogger::logInfo('Generando XML para evento de cancelación', [
                'id' => $id,
                'cdc' => $data['cdc'] ?? 'No especificado'
            ]);
            
            return $this->xmlGenerator->generateXMLEventoCancelacion($id, $params, $data, $options);
        } catch (Exception $e) {
            SifenLogger::logError('Error en FacturacionElectronicaServiceV2::generateXMLEventoCancelacion: ' . $e->getMessage());
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
    public function generateXMLEventoInutilizacion(int $id, array $params, array $data, array $options = []): string
    {
        try {
            SifenLogger::logInfo('Generando XML para evento de inutilización', [
                'id' => $id,
                'tipo_documento' => $data['tipoDocumento'] ?? 'No especificado',
                'establecimiento' => $data['establecimiento'] ?? 'No especificado',
                'punto' => $data['punto'] ?? 'No especificado',
                'numero_inicial' => $data['numeroInicial'] ?? 'No especificado',
                'numero_final' => $data['numeroFinal'] ?? 'No especificado'
            ]);
            
            return $this->xmlGenerator->generateXMLEventoInutilizacion($id, $params, $data, $options);
        } catch (Exception $e) {
            SifenLogger::logError('Error en FacturacionElectronicaServiceV2::generateXMLEventoInutilizacion: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera un CDC (Código de Control) para un documento electrónico
     *
     * @param array $params Parámetros del contribuyente
     * @param array $data Datos del documento
     * @return string CDC generado
     * @throws Exception
     */
    public function generateCDC(array $params, array $data): string
    {
        try {
            SifenLogger::logInfo('Generando CDC para documento electrónico', [
                'tipo_documento' => $data['tipoDocumento'] ?? 'No especificado',
                'establecimiento' => $data['establecimiento'] ?? 'No especificado',
                'punto' => $data['punto'] ?? 'No especificado',
                'numero' => $data['numero'] ?? 'No especificado'
            ]);
            
            return $this->cdcGenerator->generateCDC($params, $data);
        } catch (Exception $e) {
            SifenLogger::logError('Error en FacturacionElectronicaServiceV2::generateCDC: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Valida los datos de un documento electrónico según las reglas de SIFEN
     *
     * @param array $params Parámetros del contribuyente
     * @param array $data Datos del documento
     * @return array Resultado de la validación (success, errors)
     */
    public function validateData(array $params, array $data): array
    {
        try {
            SifenLogger::logInfo('Validando datos para documento electrónico', [
                'tipo_documento' => $data['tipoDocumento'] ?? 'No especificado',
                'establecimiento' => $data['establecimiento'] ?? 'No especificado',
                'punto' => $data['punto'] ?? 'No especificado',
                'numero' => $data['numero'] ?? 'No especificado'
            ]);
            
            $validator = new XmlGenerator\Helpers\XmlValidator();
            return $validator->validateData($params, $data);
        } catch (Exception $e) {
            SifenLogger::logError('Error en FacturacionElectronicaServiceV2::validateData: ' . $e->getMessage());
            return [
                'success' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }
    
    /**
     * Consulta el estado de un documento electrónico en SIFEN por su CDC
     *
     * @param string $cdc Código de Control del documento a consultar
     * @param array $options Opciones adicionales
     * @return array Información del estado del documento
     * @throws Exception
     */
    public function consultarEstadoDocumento(string $cdc, array $options = []): array
    {
        try {
            SifenLogger::logInfo('Consultando estado de documento en SIFEN', [
                'cdc' => $cdc,
                'options' => $options
            ]);

            $resultado = $this->sifenClient->consultarEstadoDocumento($cdc);

            SifenLogger::logInfo('Respuesta de SIFEN recibida', [
                'cdc' => $cdc,
                'resultado' => $resultado
            ]);

            return $resultado;
        } catch (Exception $e) {
            SifenLogger::logError('Error consultando estado en SIFEN', $e, [
                'cdc' => $cdc,
                'options' => $options
            ]);
            throw $e;
        }
    }

    /**
     * Envía un documento electrónico a SIFEN
     *
     * @param string $xml XML del documento a enviar
     * @param array $options Opciones adicionales
     * @return array Resultado del envío
     * @throws Exception
     */
    public function enviarDocumentoSIFEN(string $xml, array $options = []): array
    {
        try {
            SifenLogger::logInfo('Enviando documento a SIFEN', [
                'tamaño_xml' => strlen($xml),
                'options' => $options
            ]);

            $resultado = $this->sifenClient->enviarDocumento($xml);

            SifenLogger::logInfo('Respuesta de envío recibida', [
                'resultado' => $resultado
            ]);

            return $resultado;
        } catch (Exception $e) {
            SifenLogger::logError('Error enviando documento a SIFEN', $e, [
                'tamaño_xml' => strlen($xml),
                'options' => $options
            ]);
            throw $e;
        }
    }
}