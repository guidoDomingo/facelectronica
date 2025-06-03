<?php

namespace App\Services\FacturacionElectronica\XmlGenerator\Helpers;

use App\Services\FacturacionElectronica\SifenLogger;
use Exception;
use Carbon\Carbon;

/**
 * Clase para generar y validar Códigos de Control (CDC) para SIFEN
 */
class CdcGenerator
{
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
            // Validar datos necesarios para generar el CDC
            $this->validateCdcData($params, $data);
            
            // Obtener fecha del documento
            $fecha = Carbon::parse($data['fecha']);
            
            // Construir el CDC según el formato especificado por SIFEN
            $cdc = '';
            
            // 1. Tipo de documento (2 dígitos)
            $cdc .= str_pad($data['tipoDocumento'], 2, '0', STR_PAD_LEFT);
            
            // 2. RUC del emisor sin DV (8 dígitos)
            list($ruc, $dv) = explode('-', $params['ruc']);
            $cdc .= str_pad($ruc, 8, '0', STR_PAD_LEFT);
            
            // 3. DV del RUC (1 dígito)
            $cdc .= $dv;
            
            // 4. Establecimiento (3 dígitos)
            $cdc .= str_pad($data['establecimiento'], 3, '0', STR_PAD_LEFT);
            
            // 5. Punto de expedición (3 dígitos)
            $cdc .= str_pad($data['punto'], 3, '0', STR_PAD_LEFT);
            
            // 6. Número de documento (7 dígitos)
            $cdc .= str_pad($data['numero'], 7, '0', STR_PAD_LEFT);
            
            // 7. Tipo de emisión (1 dígito)
            $cdc .= $data['tipoEmision'] ?? '1';
            
            // 8. Código de seguridad (3 dígitos)
            $codigoSeguridad = $data['codigoSeguridadAleatorio'] ?? $this->generateRandomSecurityCode();
            $cdc .= str_pad($codigoSeguridad, 3, '0', STR_PAD_LEFT);
            
            // 9. Fecha de emisión (AAAAMMDD)
            $cdc .= $fecha->format('Ymd');
            
            // 10. Dígito verificador
            $cdc .= $this->calculateDv($cdc);
            
            SifenLogger::logInfo("CDC generado correctamente: {$cdc}");
            
            return $cdc;
        } catch (Exception $e) {
            SifenLogger::logError('Error al generar CDC: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Calcula el dígito verificador para un CDC
     *
     * @param string $cdc CDC sin el dígito verificador
     * @return string Dígito verificador
     */
    public function calculateDv(string $cdc): string
    {
        // Algoritmo de cálculo del dígito verificador según SIFEN
        $base = 11;
        $sum = 0;
        $factor = 2;
        
        // Recorrer el CDC de derecha a izquierda
        for ($i = strlen($cdc) - 1; $i >= 0; $i--) {
            $sum += intval($cdc[$i]) * $factor;
            $factor = $factor == 11 ? 2 : $factor + 1;
        }
        
        $remainder = $sum % 11;
        $dv = $remainder > 1 ? 11 - $remainder : 0;
        
        return (string) $dv;
    }
    
    /**
     * Genera un código de seguridad aleatorio de 3 dígitos
     *
     * @return string Código de seguridad
     */
    protected function generateRandomSecurityCode(): string
    {
        return str_pad((string) mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Valida que los datos necesarios para generar el CDC estén presentes
     *
     * @param array $params Parámetros del contribuyente
     * @param array $data Datos del documento
     * @throws Exception Si faltan datos requeridos
     */
    protected function validateCdcData(array $params, array $data): void
    {
        $requiredParams = ['ruc'];
        $requiredData = ['tipoDocumento', 'establecimiento', 'punto', 'numero', 'fecha'];
        
        // Verificar parámetros del contribuyente
        foreach ($requiredParams as $param) {
            if (!isset($params[$param]) || empty($params[$param])) {
                throw new Exception("Falta el parámetro requerido: {$param}");
            }
        }
        
        // Verificar datos del documento
        foreach ($requiredData as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && empty($data[$field]))) {
                throw new Exception("Falta el dato requerido: {$field}");
            }
        }
        
        // Validar formato del RUC
        if (!preg_match('/^\d+-\d+$/', $params['ruc'])) {
            throw new Exception("Formato de RUC inválido: {$params['ruc']}");
        }
    }
}