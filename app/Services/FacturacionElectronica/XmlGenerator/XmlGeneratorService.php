<?php

namespace App\Services\FacturacionElectronica\XmlGenerator;

use App\Services\FacturacionElectronica\XmlGenerator\Helpers\CdcGenerator;
use App\Services\FacturacionElectronica\XmlGenerator\Helpers\XmlValidator;
use App\Services\FacturacionElectronica\XmlGenerator\Helpers\DataFormatter;
use App\Services\FacturacionElectronica\SifenLogger;
use Exception;
use SimpleXMLElement;

/**
 * Servicio principal para generar documentos XML para SIFEN
 * 
 * Esta clase reemplaza la funcionalidad de facturacionelectronicapy-xmlgen
 * permitiendo generar documentos XML directamente desde PHP sin depender
 * del servicio Node.js.
 */
class XmlGeneratorService
{
    /**
     * Opciones por defecto para la generación de XML
     */
    protected $defaultOptions = [
        'defaultValues' => true,
        'errorSeparator' => '; ',
        'errorLimit' => 10,
        'redondeoSedeco' => true,
        'decimals' => 2,
        'taxDecimals' => 2,
        'pygDecimals' => 0,
        'partialTaxDecimals' => 8,
        'pygTaxDecimals' => 0,
        'userObjectRemove' => false,
        'test' => false,
    ];
    
    /**
     * Generador de CDC
     */
    protected $cdcGenerator;
    
    /**
     * Validador de datos
     */
    protected $validator;
    
    /**
     * Formateador de datos
     */
    protected $formatter;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cdcGenerator = new CdcGenerator();
        $this->validator = new XmlValidator();
        $this->formatter = new DataFormatter();
    }
    
    /**
     * Genera un XML para el documento electrónico
     * 
     * @param array $params Parámetros estáticos del Contribuyente emisor
     * @param array $data Datos variables para el documento electrónico
     * @param array $options Opciones adicionales
     * @return string XML generado
     * @throws Exception
     */
    public function generateXMLDE(array $params, array $data, array $options = []): string
    {
        try {
            // Combinar opciones por defecto con las recibidas
            $options = array_merge($this->defaultOptions, $options);
            
            // Validar datos de entrada
            $validationResult = $this->validator->validateData($params, $data);
            if (!$validationResult['success']) {
                throw new Exception('Error de validación: ' . implode($options['errorSeparator'], $validationResult['errors']));
            }
            
            // Generar CDC si no se proporcionó
            if (empty($data['cdc'])) {
                $data['cdc'] = $this->cdcGenerator->generateCDC($params, $data);
            }
            
            // Crear estructura XML base
            $xml = $this->createBaseXml($params['version']);
            
            // Agregar datos del documento electrónico
            $this->addDocumentData($xml, $params, $data, $options);
            
            // Formatear y devolver el XML como string
            return $this->formatXml($xml);
        } catch (Exception $e) {
            SifenLogger::logError('Error al generar XML: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera un XML para un evento de cancelación
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
            // Combinar opciones por defecto con las recibidas
            $options = array_merge($this->defaultOptions, $options);
            
            // Validar datos de entrada para evento de cancelación
            $validationResult = $this->validator->validateEventData($params, $data, 'cancelacion');
            if (!$validationResult['success']) {
                throw new Exception('Error de validación: ' . implode($options['errorSeparator'], $validationResult['errors']));
            }
            
            // Crear estructura XML base para evento
            $xml = $this->createBaseEventXml($params['version']);
            
            // Agregar datos del evento de cancelación
            $this->addCancelacionEventData($xml, $id, $params, $data, $options);
            
            // Formatear y devolver el XML como string
            return $this->formatXml($xml);
        } catch (Exception $e) {
            SifenLogger::logError('Error al generar XML de evento de cancelación: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera un XML para un evento de inutilización
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
            // Combinar opciones por defecto con las recibidas
            $options = array_merge($this->defaultOptions, $options);
            
            // Validar datos de entrada para evento de inutilización
            $validationResult = $this->validator->validateEventData($params, $data, 'inutilizacion');
            if (!$validationResult['success']) {
                throw new Exception('Error de validación: ' . implode($options['errorSeparator'], $validationResult['errors']));
            }
            
            // Crear estructura XML base para evento
            $xml = $this->createBaseEventXml($params['version']);
            
            // Agregar datos del evento de inutilización
            $this->addInutilizacionEventData($xml, $id, $params, $data, $options);
            
            // Formatear y devolver el XML como string
            return $this->formatXml($xml);
        } catch (Exception $e) {
            SifenLogger::logError('Error al generar XML de evento de inutilización: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Crea la estructura base del XML para documentos electrónicos
     * 
     * @param int $version Versión del esquema
     * @return SimpleXMLElement
     */
    protected function createBaseXml(int $version): SimpleXMLElement
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rDE></rDE>');
        $xml->addAttribute('xmlns', 'http://ekuatia.set.gov.py/sifen/xsd');
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('xsi:schemaLocation', 'http://ekuatia.set.gov.py/sifen/xsd siRecepDE_v' . $version . '.xsd');
        
        return $xml;
    }
    
    /**
     * Crea la estructura base del XML para eventos
     * 
     * @param int $version Versión del esquema
     * @return SimpleXMLElement
     */
    protected function createBaseEventXml(int $version): SimpleXMLElement
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rEvento></rEvento>');
        $xml->addAttribute('xmlns', 'http://ekuatia.set.gov.py/sifen/xsd');
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('xsi:schemaLocation', 'http://ekuatia.set.gov.py/sifen/xsd siRecepEvento_v' . $version . '.xsd');
        
        return $xml;
    }
    
    /**
     * Agrega los datos del documento electrónico al XML
     * 
     * @param SimpleXMLElement $xml Elemento XML base
     * @param array $params Parámetros del contribuyente
     * @param array $data Datos del documento
     * @param array $options Opciones de generación
     */
    protected function addDocumentData(SimpleXMLElement $xml, array $params, array $data, array $options): void
    {
        // Implementación de la estructura completa del XML según el manual técnico de SIFEN
        // Esta es una implementación básica que debe ser completada con todos los campos
        // requeridos según la documentación oficial
        
        // Datos del DE
        $de = $xml->addChild('DE');
        
        // Agregar encabezado
        $this->addHeader($de, $params, $data);
        
        // Agregar datos del emisor
        $this->addEmisorData($de, $params);
        
        // Agregar datos del receptor
        if (isset($data['cliente'])) {
            $this->addReceptorData($de, $data['cliente']);
        }
        
        // Agregar condición de la operación
        if (isset($data['condicion'])) {
            $this->addCondicionOperacion($de, $data['condicion']);
        }
        
        // Agregar detalles de la operación (ítems)
        if (isset($data['items']) && is_array($data['items'])) {
            $this->addItems($de, $data['items'], $options);
        }
    }
    
    /**
     * Agrega los datos del encabezado al XML
     * 
     * @param SimpleXMLElement $de Elemento DE del XML
     * @param array $params Parámetros del contribuyente
     * @param array $data Datos del documento
     */
    protected function addHeader(SimpleXMLElement $de, array $params, array $data): void
    {
        // Implementación del encabezado según el manual técnico
        $dDVId = $de->addChild('dDVId', $this->cdcGenerator->calculateDv($data['cdc']));
        
        $dFecFirma = $de->addChild('dFecFirma', $this->formatter->formatDateTime($data['fecha']));
        
        $dSisFact = $de->addChild('dSisFact', $data['tipoEmision'] ?? 1);
    }
    
    /**
     * Agrega los datos del emisor al XML
     * 
     * @param SimpleXMLElement $de Elemento DE del XML
     * @param array $params Parámetros del contribuyente
     */
    protected function addEmisorData(SimpleXMLElement $de, array $params): void
    {
        // Implementación de los datos del emisor según el manual técnico
        $emisor = $de->addChild('Emisor');
        
        // RUC del emisor
        $rucEm = $emisor->addChild('RespOfe');
        list($ruc, $dv) = explode('-', $params['ruc']);
        $rucEm->addChild('dRucEm', $ruc);
        $rucEm->addChild('dDVEmi', $dv);
        
        // Datos del emisor
        $emisor->addChild('dNomEmi', $params['razonSocial']);
        
        if (!empty($params['nombreFantasia'])) {
            $emisor->addChild('dNomFanEmi', $params['nombreFantasia']);
        }
        
        // Dirección del emisor
        if (isset($params['establecimientos']) && !empty($params['establecimientos'])) {
            $establecimiento = $params['establecimientos'][0];
            $this->addDireccion($emisor, $establecimiento);
        }
        
        // Actividades económicas
        if (isset($params['actividadesEconomicas']) && !empty($params['actividadesEconomicas'])) {
            $actividadEconomica = $params['actividadesEconomicas'][0];
            $emisor->addChild('cActEco', $actividadEconomica['codigo']);
        }
    }
    
    /**
     * Agrega los datos del receptor al XML
     * 
     * @param SimpleXMLElement $de Elemento DE del XML
     * @param array $cliente Datos del cliente/receptor
     */
    protected function addReceptorData(SimpleXMLElement $de, array $cliente): void
    {
        // Implementación de los datos del receptor según el manual técnico
        $receptor = $de->addChild('Receptor');
        
        // Contribuyente o no
        if ($cliente['contribuyente']) {
            // RUC del receptor
            list($ruc, $dv) = explode('-', $cliente['ruc']);
            $receptor->addChild('dRucRec', $ruc);
            $receptor->addChild('dDVRec', $dv);
        } else {
            // Documento de identidad para no contribuyentes
            $receptor->addChild('iTipIDRec', $cliente['documentoTipo']);
            $receptor->addChild('dNumIDRec', $cliente['documentoNumero']);
        }
        
        // Datos del receptor
        $receptor->addChild('dNomRec', $cliente['razonSocial']);
        
        if (!empty($cliente['nombreFantasia'])) {
            $receptor->addChild('dNomFanRec', $cliente['nombreFantasia']);
        }
        
        // Dirección del receptor
        if (!empty($cliente['direccion'])) {
            $this->addDireccion($receptor, $cliente);
        }
    }
    
    /**
     * Agrega los datos de dirección al XML
     * 
     * @param SimpleXMLElement $parent Elemento padre al que agregar la dirección
     * @param array $datos Datos de la dirección
     */
    protected function addDireccion(SimpleXMLElement $parent, array $datos): void
    {
        $direccion = $parent->addChild('Direccion');
        
        if (!empty($datos['direccion'])) {
            $direccion->addChild('dDirPri', $datos['direccion']);
        }
        
        if (!empty($datos['numeroCasa'])) {
            $direccion->addChild('dNumCas', $datos['numeroCasa']);
        }
        
        if (!empty($datos['complementoDireccion1'])) {
            $direccion->addChild('dCompDir1', $datos['complementoDireccion1']);
        }
        
        if (!empty($datos['complementoDireccion2'])) {
            $direccion->addChild('dCompDir2', $datos['complementoDireccion2']);
        }
        
        if (!empty($datos['departamento'])) {
            $direccion->addChild('cDepDir', $datos['departamento']);
            
            if (!empty($datos['departamentoDescripcion'])) {
                $direccion->addChild('dDesDepDir', $datos['departamentoDescripcion']);
            }
        }
        
        if (!empty($datos['distrito'])) {
            $direccion->addChild('cDisDir', $datos['distrito']);
            
            if (!empty($datos['distritoDescripcion'])) {
                $direccion->addChild('dDesDisDir', $datos['distritoDescripcion']);
            }
        }
        
        if (!empty($datos['ciudad'])) {
            $direccion->addChild('cCiuDir', $datos['ciudad']);
            
            if (!empty($datos['ciudadDescripcion'])) {
                $direccion->addChild('dDesCiuDir', $datos['ciudadDescripcion']);
            }
        }
    }
    
    /**
     * Agrega la condición de la operación al XML
     * 
     * @param SimpleXMLElement $de Elemento DE del XML
     * @param array $condicion Datos de la condición de operación
     */
    protected function addCondicionOperacion(SimpleXMLElement $de, array $condicion): void
    {
        // Implementación de la condición de operación según el manual técnico
        $condicionOp = $de->addChild('CondOpe');
        
        // Tipo de condición (contado, crédito, etc.)
        $condicionOp->addChild('iTipTra', $condicion['tipo'] ?? 1);
        
        // Condición de pago
        if (isset($condicion['credito']) && $condicion['tipo'] == 2) {
            $this->addCondicionCredito($condicionOp, $condicion['credito']);
        }
    }
    
    /**
     * Agrega la condición de crédito al XML
     * 
     * @param SimpleXMLElement $condicionOp Elemento CondOpe del XML
     * @param array $credito Datos del crédito
     */
    protected function addCondicionCredito(SimpleXMLElement $condicionOp, array $credito): void
    {
        $condicionCredito = $condicionOp->addChild('Credito');
        
        if (isset($credito['plazo'])) {
            $condicionCredito->addChild('iCondCred', $credito['plazo']);
        }
        
        if (isset($credito['cuotas']) && is_array($credito['cuotas'])) {
            $this->addCuotas($condicionCredito, $credito['cuotas']);
        }
    }
    
    /**
     * Agrega las cuotas al XML
     * 
     * @param SimpleXMLElement $condicionCredito Elemento Credito del XML
     * @param array $cuotas Lista de cuotas
     */
    protected function addCuotas(SimpleXMLElement $condicionCredito, array $cuotas): void
    {
        $gCuotas = $condicionCredito->addChild('gCuotas');
        
        foreach ($cuotas as $index => $cuota) {
            $gCuotaItem = $gCuotas->addChild('gCuotaItem');
            $gCuotaItem->addChild('cMoneCuo', $cuota['moneda'] ?? 'PYG');
            $gCuotaItem->addChild('dMonCuota', $this->formatter->formatAmount($cuota['monto'], 2));
            
            if (isset($cuota['vencimiento'])) {
                $gCuotaItem->addChild('dVencCuo', $this->formatter->formatDate($cuota['vencimiento']));
            }
        }
    }
    
    /**
     * Agrega los ítems al XML
     * 
     * @param SimpleXMLElement $de Elemento DE del XML
     * @param array $items Lista de ítems
     * @param array $options Opciones de generación
     */
    protected function addItems(SimpleXMLElement $de, array $items, array $options): void
    {
        $detalles = $de->addChild('Items');
        
        foreach ($items as $index => $item) {
            $itemElement = $detalles->addChild('Item');
            
            // Número de orden del ítem
            $itemElement->addChild('dCodInt', $item['codigo'] ?? ($index + 1));
            
            // Descripción del ítem
            $itemElement->addChild('dDesProSer', $item['descripcion']);
            
            // Cantidad
            $itemElement->addChild('dCantProSer', $this->formatter->formatAmount($item['cantidad'], $options['decimals']));
            
            // Unidad de medida
            if (isset($item['unidadMedida'])) {
                $itemElement->addChild('cUniMed', $item['unidadMedida']);
            }
            
            // Precio unitario
            $itemElement->addChild('dPrecioUni', $this->formatter->formatAmount($item['precioUnitario'], $options['decimals']));
            
            // Agregar información de IVA y otros impuestos
            if (isset($item['iva'])) {
                $this->addIvaItem($itemElement, $item, $options);
            }
        }
    }
    
    /**
     * Agrega la información de IVA al ítem
     * 
     * @param SimpleXMLElement $itemElement Elemento Item del XML
     * @param array $item Datos del ítem
     * @param array $options Opciones de generación
     */
    protected function addIvaItem(SimpleXMLElement $itemElement, array $item, array $options): void
    {
        // Implementación de la información de IVA según el manual técnico
        $iva = $itemElement->addChild('IVA');
        
        // Tipo de IVA (exento, 5%, 10%, etc.)
        $iva->addChild('iAfecIVA', $item['iva']['tipo']);
        
        // Porcentaje de IVA
        $iva->addChild('dPropIVA', $this->formatter->formatAmount($item['iva']['porcentaje'], 2));
        
        // Base imponible
        $iva->addChild('dBasGravIVA', $this->formatter->formatAmount($item['iva']['base'], $options['decimals']));
        
        // Monto de IVA
        $iva->addChild('dLiqIVAItem', $this->formatter->formatAmount($item['iva']['monto'], $options['taxDecimals']));
    }
    
    /**
     * Agrega los datos del evento de cancelación al XML
     * 
     * @param SimpleXMLElement $xml Elemento XML base
     * @param int $id ID del evento
     * @param array $params Parámetros del contribuyente
     * @param array $data Datos del evento
     * @param array $options Opciones de generación
     */
    protected function addCancelacionEventData(SimpleXMLElement $xml, int $id, array $params, array $data, array $options): void
    {
        // Implementación de los datos del evento de cancelación según el manual técnico
        // Esta es una implementación básica que debe ser completada según la documentación oficial
        
        // Datos del evento
        $evento = $xml->addChild('gEvento');
        
        // Identificador del evento
        $evento->addChild('Id', $id);
        
        // Datos del emisor
        $this->addEmisorEventData($evento, $params);
        
        // Datos del documento a cancelar
        $this->addDocumentoCancelacionData($evento, $data);
        
        // Motivo de la cancelación
        $evento->addChild('dMotEve', $data['motivo']);
    }
    
    /**
     * Agrega los datos del evento de inutilización al XML
     * 
     * @param SimpleXMLElement $xml Elemento XML base
     * @param int $id ID del evento
     * @param array $params Parámetros del contribuyente
     * @param array $data Datos del evento
     * @param array $options Opciones de generación
     */
    protected function addInutilizacionEventData(SimpleXMLElement $xml, int $id, array $params, array $data, array $options): void
    {
        // Implementación de los datos del evento de inutilización según el manual técnico
        // Esta es una implementación básica que debe ser completada según la documentación oficial
        
        // Datos del evento
        $evento = $xml->addChild('gEvento');
        
        // Identificador del evento
        $evento->addChild('Id', $id);
        
        // Datos del emisor
        $this->addEmisorEventData($evento, $params);
        
        // Datos del rango de documentos a inutilizar
        $this->addRangoInutilizacionData($evento, $data);
        
        // Motivo de la inutilización
        $evento->addChild('dMotEve', $data['motivo']);
    }
    
    /**
     * Agrega los datos del emisor para eventos al XML
     * 
     * @param SimpleXMLElement $evento Elemento gEvento del XML
     * @param array $params Parámetros del contribuyente
     */
    protected function addEmisorEventData(SimpleXMLElement $evento, array $params): void
    {
        // Implementación de los datos del emisor para eventos según el manual técnico
        $emisor = $evento->addChild('gEmis');
        
        // RUC del emisor
        list($ruc, $dv) = explode('-', $params['ruc']);
        $emisor->addChild('dRucEm', $ruc);
        $emisor->addChild('dDVEmi', $dv);
    }
    
    /**
     * Agrega los datos del documento a cancelar al XML
     * 
     * @param SimpleXMLElement $evento Elemento gEvento del XML
     * @param array $data Datos del evento
     */
    protected function addDocumentoCancelacionData(SimpleXMLElement $evento, array $data): void
    {
        // Implementación de los datos del documento a cancelar según el manual técnico
        $documento = $evento->addChild('gDatGralOpe');
        
        // CDC del documento a cancelar
        $documento->addChild('dCdCDERef', $data['cdc']);
    }
    
    /**
     * Agrega los datos del rango de documentos a inutilizar al XML
     * 
     * @param SimpleXMLElement $evento Elemento gEvento del XML
     * @param array $data Datos del evento
     */
    protected function addRangoInutilizacionData(SimpleXMLElement $evento, array $data): void
    {
        // Implementación de los datos del rango de documentos a inutilizar según el manual técnico
        $rango = $evento->addChild('gDatGralOpe');
        
        // Tipo de documento
        $rango->addChild('iTipDE', $data['tipoDocumento']);
        
        // Establecimiento
        $rango->addChild('dEstDE', $data['establecimiento']);
        
        // Punto de expedición
        $rango->addChild('dPunExpDE', $data['punto']);
        
        // Número de documento inicial
        $rango->addChild('dNumDocIni', $data['numeroInicial']);
        
        // Número de documento final
        $rango->addChild('dNumDocFin', $data['numeroFinal']);
    }
    
    /**
     * Formatea el XML para su salida
     * 
     * @param SimpleXMLElement $xml Elemento XML
     * @return string XML formateado
     */
    protected function formatXml(SimpleXMLElement $xml): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        
        return $dom->saveXML();
    }
}