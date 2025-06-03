<?php

namespace App\Services\FacturacionElectronica\XmlGenerator\Helpers;

use App\Services\FacturacionElectronica\SifenLogger;
use Exception;
use Carbon\Carbon;

/**
 * Clase para validar datos según las reglas de SIFEN
 */
class XmlValidator
{
    /**
     * Valida los datos para la generación de un documento electrónico
     *
     * @param array $params Parámetros del contribuyente
     * @param array $data Datos del documento
     * @return array Resultado de la validación (success, errors)
     */
    public function validateData(array $params, array $data): array
    {
        $errors = [];
        
        // Validar parámetros del contribuyente
        $errors = array_merge($errors, $this->validateContribuyenteParams($params));
        
        // Validar datos del documento
        $errors = array_merge($errors, $this->validateDocumentoData($data));
        
        // Validar datos del cliente/receptor si están presentes
        if (isset($data['cliente']) && is_array($data['cliente'])) {
            $errors = array_merge($errors, $this->validateClienteData($data['cliente']));
        }
        
        // Validar ítems si están presentes
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                $itemErrors = $this->validateItemData($item);
                foreach ($itemErrors as $error) {
                    $errors[] = "Ítem #{$index}: {$error}";
                }
            }
        }
        
        return [
            'success' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Valida los datos para la generación de un evento
     *
     * @param array $params Parámetros del contribuyente
     * @param array $data Datos del evento
     * @param string $tipoEvento Tipo de evento (cancelacion, inutilizacion, etc.)
     * @return array Resultado de la validación (success, errors)
     */
    public function validateEventData(array $params, array $data, string $tipoEvento): array
    {
        $errors = [];
        
        // Validar parámetros del contribuyente (básicos)
        if (!isset($params['ruc']) || empty($params['ruc'])) {
            $errors[] = "Falta el RUC del contribuyente";
        } elseif (!preg_match('/^\d+-\d+$/', $params['ruc'])) {
            $errors[] = "Formato de RUC inválido: {$params['ruc']}";
        }
        
        // Validar datos específicos según el tipo de evento
        switch ($tipoEvento) {
            case 'cancelacion':
                $errors = array_merge($errors, $this->validateCancelacionData($data));
                break;
                
            case 'inutilizacion':
                $errors = array_merge($errors, $this->validateInutilizacionData($data));
                break;
                
            case 'conformidad':
                $errors = array_merge($errors, $this->validateConformidadData($data));
                break;
                
            default:
                $errors[] = "Tipo de evento no soportado: {$tipoEvento}";
        }
        
        return [
            'success' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Valida los parámetros del contribuyente
     *
     * @param array $params Parámetros del contribuyente
     * @return array Errores encontrados
     */
    protected function validateContribuyenteParams(array $params): array
    {
        $errors = [];
        
        // Validar campos requeridos
        $requiredFields = [
            'ruc' => 'RUC',
            'razonSocial' => 'Razón Social',
            'timbradoNumero' => 'Número de Timbrado',
            'timbradoFecha' => 'Fecha de Timbrado',
            'tipoContribuyente' => 'Tipo de Contribuyente',
            'tipoRegimen' => 'Tipo de Régimen'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (!isset($params[$field]) || (is_string($params[$field]) && empty($params[$field]))) {
                $errors[] = "Falta el campo {$label}";
            }
        }
        
        // Validar formato del RUC
        if (isset($params['ruc']) && !empty($params['ruc'])) {
            if (!preg_match('/^\d+-\d+$/', $params['ruc'])) {
                $errors[] = "Formato de RUC inválido: {$params['ruc']}";
            }
        }
        
        // Validar fecha de timbrado
        if (isset($params['timbradoFecha']) && !empty($params['timbradoFecha'])) {
            try {
                $fecha = Carbon::parse($params['timbradoFecha']);
                
                // Verificar que la fecha no sea futura
                if ($fecha->isAfter(Carbon::now())) {
                    $errors[] = "La fecha de timbrado no puede ser futura";
                }
            } catch (Exception $e) {
                $errors[] = "Formato de fecha de timbrado inválido: {$params['timbradoFecha']}";
            }
        }
        
        // Validar establecimientos
        if (!isset($params['establecimientos']) || !is_array($params['establecimientos']) || empty($params['establecimientos'])) {
            $errors[] = "Debe proporcionar al menos un establecimiento";
        } else {
            foreach ($params['establecimientos'] as $index => $establecimiento) {
                if (!isset($establecimiento['codigo']) || empty($establecimiento['codigo'])) {
                    $errors[] = "Falta el código del establecimiento #{$index}";
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Valida los datos del documento
     *
     * @param array $data Datos del documento
     * @return array Errores encontrados
     */
    protected function validateDocumentoData(array $data): array
    {
        $errors = [];
        
        // Validar campos requeridos
        $requiredFields = [
            'tipoDocumento' => 'Tipo de Documento',
            'establecimiento' => 'Establecimiento',
            'punto' => 'Punto de Expedición',
            'numero' => 'Número de Documento',
            'fecha' => 'Fecha de Emisión'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (!isset($data[$field]) || (is_string($data[$field]) && empty($data[$field]))) {
                $errors[] = "Falta el campo {$label}";
            }
        }
        
        // Validar tipo de documento
        if (isset($data['tipoDocumento'])) {
            $tiposValidos = [1, 2, 3, 4, 5, 6, 7, 8]; // Según manual técnico SIFEN
            if (!in_array($data['tipoDocumento'], $tiposValidos)) {
                $errors[] = "Tipo de documento inválido: {$data['tipoDocumento']}";
            }
        }
        
        // Validar formato de establecimiento
        if (isset($data['establecimiento']) && !empty($data['establecimiento'])) {
            if (!preg_match('/^\d{1,3}$/', $data['establecimiento'])) {
                $errors[] = "Formato de establecimiento inválido: {$data['establecimiento']}";
            }
        }
        
        // Validar formato de punto de expedición
        if (isset($data['punto']) && !empty($data['punto'])) {
            if (!preg_match('/^\d{1,3}$/', $data['punto'])) {
                $errors[] = "Formato de punto de expedición inválido: {$data['punto']}";
            }
        }
        
        // Validar formato de número de documento
        if (isset($data['numero']) && !empty($data['numero'])) {
            if (!preg_match('/^\d{1,7}$/', $data['numero'])) {
                $errors[] = "Formato de número de documento inválido: {$data['numero']}";
            }
        }
        
        // Validar fecha de emisión
        if (isset($data['fecha']) && !empty($data['fecha'])) {
            try {
                $fecha = Carbon::parse($data['fecha']);
                
                // Verificar que la fecha no sea futura (con margen de 1 día)
                if ($fecha->isAfter(Carbon::now()->addDay())) {
                    $errors[] = "La fecha de emisión no puede ser futura (con margen de 1 día)";
                }
                
                // Verificar que la fecha no sea muy antigua (máximo 6 meses atrás según SIFEN)
                if ($fecha->isBefore(Carbon::now()->subMonths(6))) {
                    $errors[] = "La fecha de emisión no puede ser anterior a 6 meses";
                }
            } catch (Exception $e) {
                $errors[] = "Formato de fecha de emisión inválido: {$data['fecha']}";
            }
        }
        
        return $errors;
    }
    
    /**
     * Valida los datos del cliente/receptor
     *
     * @param array $cliente Datos del cliente
     * @return array Errores encontrados
     */
    protected function validateClienteData(array $cliente): array
    {
        $errors = [];
        
        // Validar campos requeridos
        $requiredFields = [
            'razonSocial' => 'Razón Social del Cliente'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (!isset($cliente[$field]) || (is_string($cliente[$field]) && empty($cliente[$field]))) {
                $errors[] = "Falta el campo {$label}";
            }
        }
        
        // Validar RUC si es contribuyente
        if (isset($cliente['contribuyente']) && $cliente['contribuyente']) {
            if (!isset($cliente['ruc']) || empty($cliente['ruc'])) {
                $errors[] = "Falta el RUC del cliente";
            } elseif (!preg_match('/^\d+-\d+$/', $cliente['ruc'])) {
                $errors[] = "Formato de RUC del cliente inválido: {$cliente['ruc']}";
            }
        } else {
            // Validar documento si no es contribuyente
            if (!isset($cliente['documentoTipo']) || empty($cliente['documentoTipo'])) {
                $errors[] = "Falta el tipo de documento del cliente";
            }
            
            if (!isset($cliente['documentoNumero']) || empty($cliente['documentoNumero'])) {
                $errors[] = "Falta el número de documento del cliente";
            }
        }
        
        return $errors;
    }
    
    /**
     * Valida los datos de un ítem
     *
     * @param array $item Datos del ítem
     * @return array Errores encontrados
     */
    protected function validateItemData(array $item): array
    {
        $errors = [];
        
        // Validar campos requeridos
        $requiredFields = [
            'descripcion' => 'Descripción',
            'cantidad' => 'Cantidad',
            'precioUnitario' => 'Precio Unitario'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (!isset($item[$field]) || (is_string($item[$field]) && empty($item[$field]))) {
                $errors[] = "Falta el campo {$label}";
            }
        }
        
        // Validar cantidad
        if (isset($item['cantidad'])) {
            if (!is_numeric($item['cantidad']) || $item['cantidad'] <= 0) {
                $errors[] = "La cantidad debe ser un número mayor que cero";
            }
        }
        
        // Validar precio unitario
        if (isset($item['precioUnitario'])) {
            if (!is_numeric($item['precioUnitario']) || $item['precioUnitario'] < 0) {
                $errors[] = "El precio unitario debe ser un número mayor o igual a cero";
            }
        }
        
        // Validar información de IVA si está presente
        if (isset($item['iva']) && is_array($item['iva'])) {
            if (!isset($item['iva']['tipo']) || empty($item['iva']['tipo'])) {
                $errors[] = "Falta el tipo de IVA";
            }
            
            if (!isset($item['iva']['base']) || !is_numeric($item['iva']['base'])) {
                $errors[] = "Falta la base imponible del IVA o no es un número válido";
            }
            
            if (!isset($item['iva']['porcentaje']) || !is_numeric($item['iva']['porcentaje'])) {
                $errors[] = "Falta el porcentaje de IVA o no es un número válido";
            }
        }
        
        return $errors;
    }
    
    /**
     * Valida los datos para un evento de cancelación
     *
     * @param array $data Datos del evento
     * @return array Errores encontrados
     */
    protected function validateCancelacionData(array $data): array
    {
        $errors = [];
        
        // Validar CDC del documento a cancelar
        if (!isset($data['cdc']) || empty($data['cdc'])) {
            $errors[] = "Falta el CDC del documento a cancelar";
        } elseif (strlen($data['cdc']) != 44) {
            $errors[] = "El CDC debe tener 44 caracteres";
        }
        
        // Validar motivo de cancelación
        if (!isset($data['motivo']) || empty($data['motivo'])) {
            $errors[] = "Falta el motivo de cancelación";
        }
        
        return $errors;
    }
    
    /**
     * Valida los datos para un evento de inutilización
     *
     * @param array $data Datos del evento
     * @return array Errores encontrados
     */
    protected function validateInutilizacionData(array $data): array
    {
        $errors = [];
        
        // Validar campos requeridos
        $requiredFields = [
            'tipoDocumento' => 'Tipo de Documento',
            'establecimiento' => 'Establecimiento',
            'punto' => 'Punto de Expedición',
            'numeroInicial' => 'Número Inicial',
            'numeroFinal' => 'Número Final',
            'motivo' => 'Motivo de Inutilización'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (!isset($data[$field]) || (is_string($data[$field]) && empty($data[$field]))) {
                $errors[] = "Falta el campo {$label}";
            }
        }
        
        // Validar que el número final sea mayor o igual al inicial
        if (isset($data['numeroInicial']) && isset($data['numeroFinal'])) {
            if (intval($data['numeroFinal']) < intval($data['numeroInicial'])) {
                $errors[] = "El número final debe ser mayor o igual al número inicial";
            }
        }
        
        return $errors;
    }
    
    /**
     * Valida los datos para un evento de conformidad
     *
     * @param array $data Datos del evento
     * @return array Errores encontrados
     */
    protected function validateConformidadData(array $data): array
    {
        $errors = [];
        
        // Validar CDC del documento
        if (!isset($data['cdc']) || empty($data['cdc'])) {
            $errors[] = "Falta el CDC del documento";
        } elseif (strlen($data['cdc']) != 44) {
            $errors[] = "El CDC debe tener 44 caracteres";
        }
        
        return $errors;
    }
}