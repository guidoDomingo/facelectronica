<?php

namespace App\Services\FacturacionElectronica;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Servicio para validar estructuras XML contra esquemas XSD de SIFEN
 */
class SifenValidatorService
{
    /**
     * Ruta base donde se almacenarán los esquemas XSD
     * 
     * @var string
     */
    protected $schemasPath;
    
    /**
     * Lista de esquemas disponibles para validación
     * 
     * @var array
     */
    protected $availableSchemas = [
        'de' => 'siRecepDE_v150.xsd',
        'evento' => 'siRecepEvento_v150.xsd',
        'consulta' => 'siConsDE_v150.xsd'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->schemasPath = storage_path('app/xsd');
    }
    
    /**
     * Valida un XML contra el esquema XSD correspondiente
     *
     * @param string $xml El XML a validar
     * @param string $schemaType Tipo de esquema a usar (de, evento, consulta)
     * @return array Resultado de la validación (success, errors)
     */
    public function validateXML(string $xml, string $schemaType = 'de')
    {
        try {
            // Verificar que se haya proporcionado un esquema válido
            if (!isset($this->availableSchemas[$schemaType])) {
                throw new Exception("Tipo de esquema no válido: {$schemaType}");
            }
            
            $schemaFile = $this->getSchemaPath($schemaType);
            
            // Registrar la operación
            SifenLogger::logInfo("Validando XML contra esquema {$schemaType}", [
                'schema_file' => $schemaFile,
                'xml_length' => strlen($xml)
            ]);
            
            // Crear objetos para validación
            $xmlDoc = new \DOMDocument();
            $xmlDoc->loadXML($xml);

            // Usar libxml para capturar errores
            libxml_use_internal_errors(true);
            
            // Realizar la validación
            $valid = $xmlDoc->schemaValidate($schemaFile);
            
            if ($valid) {
                return [
                    'success' => true,
                    'message' => 'XML válido según esquema ' . $schemaType
                ];
            } else {
                $errors = libxml_get_errors();
                $errorMessages = [];
                
                foreach ($errors as $error) {
                    $errorMessages[] = $this->formatLibXmlError($error);
                }
                
                libxml_clear_errors();
                
                SifenLogger::logInfo("Validación XML fallida", [
                    'schema_type' => $schemaType,
                    'errors' => $errorMessages
                ]);
                
                return [
                    'success' => false,
                    'message' => 'XML no válido según esquema ' . $schemaType,
                    'errors' => $errorMessages
                ];
            }
        } catch (Exception $e) {
            SifenLogger::logError('Error al validar XML', $e, [
                'schema_type' => $schemaType
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al validar XML: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtiene la ruta al archivo XSD o lo descarga si no existe
     *
     * @param string $schemaType Tipo de esquema
     * @return string Ruta al archivo XSD
     * @throws Exception Si no se puede obtener el esquema
     */
    protected function getSchemaPath(string $schemaType)
    {
        $schemaFile = $this->schemasPath . '/' . $this->availableSchemas[$schemaType];
        
        // Verificar si el archivo existe
        if (!file_exists($schemaFile)) {
            // Crear el directorio si no existe
            if (!file_exists($this->schemasPath)) {
                mkdir($this->schemasPath, 0755, true);
            }
            
            // Intentar descargar el esquema
            $schemaUrl = $this->getSchemaUrl($schemaType);
            $downloaded = $this->downloadSchema($schemaUrl, $schemaFile);
            
            if (!$downloaded) {
                throw new Exception("No se pudo obtener el esquema XSD para {$schemaType}");
            }
        }
        
        return $schemaFile;
    }
    
    /**
     * Obtiene la URL del esquema XSD según el tipo
     *
     * @param string $schemaType Tipo de esquema
     * @return string URL del esquema
     */
    protected function getSchemaUrl(string $schemaType)
    {
        // URLs de los esquemas XSD de SIFEN
        $baseUrl = 'https://ekuatia.set.gov.py/portal/ekuatia/documentacion/documentacion-tecnica';
        
        // En un caso real, estas URLs podrían estar en una configuración
        // o podrían ser enlaces directos a los XSD en el sitio de SIFEN
        switch ($schemaType) {
            case 'de':
                return $baseUrl . '/siRecepDE_v150.xsd';
            case 'evento':
                return $baseUrl . '/siRecepEvento_v150.xsd';
            case 'consulta':
                return $baseUrl . '/siConsDE_v150.xsd';
            default:
                return '';
        }
    }
    
    /**
     * Descarga un esquema XSD
     *
     * @param string $url URL del esquema
     * @param string $destination Ruta de destino
     * @return bool Si la descarga fue exitosa
     */
    protected function downloadSchema($url, $destination)
    {
        try {
            // En un entorno real, esto debería usar HTTP client para descargar el archivo
            // Como alternativa, podríamos incluir los XSD en el repositorio del proyecto
            
            // Ejemplo de descarga usando file_get_contents
            $content = file_get_contents($url);
            if ($content === false) {
                return false;
            }
            
            // Guardar el contenido
            file_put_contents($destination, $content);
            
            return file_exists($destination);
        } catch (Exception $e) {
            Log::error('Error al descargar esquema XSD: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Formatea un error de libxml
     *
     * @param \LibXMLError $error Error de libxml
     * @return string Mensaje de error formateado
     */
    protected function formatLibXmlError(\LibXMLError $error)
    {
        $return = '';
        
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Advertencia $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "Error fatal $error->code: ";
                break;
        }
        
        $return .= trim($error->message);
        
        if ($error->line > 0) {
            $return .= " en la línea $error->line";
        }
        
        return $return;
    }
}
