<?php

namespace App\Services\FacturacionElectronica\XmlGenerator\Helpers;

use Carbon\Carbon;
use Exception;

/**
 * Clase para formatear datos según los requisitos de SIFEN
 */
class DataFormatter
{
    /**
     * Formatea un número como monto con la cantidad de decimales especificada
     *
     * @param float|string $amount Monto a formatear
     * @param int $decimals Cantidad de decimales
     * @return string Monto formateado
     */
    public function formatAmount($amount, int $decimals = 2): string
    {
        // Convertir a float si es string
        if (is_string($amount)) {
            $amount = (float) str_replace(',', '.', $amount);
        }
        
        // Formatear con la cantidad de decimales especificada
        return number_format($amount, $decimals, '.', '');
    }
    
    /**
     * Formatea una fecha en el formato requerido por SIFEN (YYYY-MM-DD)
     *
     * @param string|Carbon $date Fecha a formatear
     * @return string Fecha formateada
     * @throws Exception
     */
    public function formatDate($date): string
    {
        try {
            if ($date instanceof Carbon) {
                return $date->format('Y-m-d');
            }
            
            return Carbon::parse($date)->format('Y-m-d');
        } catch (Exception $e) {
            throw new Exception("Error al formatear fecha: {$e->getMessage()}");
        }
    }
    
    /**
     * Formatea una fecha y hora en el formato requerido por SIFEN (YYYY-MM-DDThh:mm:ss)
     *
     * @param string|Carbon $dateTime Fecha y hora a formatear
     * @return string Fecha y hora formateada
     * @throws Exception
     */
    public function formatDateTime($dateTime): string
    {
        try {
            if ($dateTime instanceof Carbon) {
                return $dateTime->format('Y-m-d\TH:i:s');
            }
            
            return Carbon::parse($dateTime)->format('Y-m-d\TH:i:s');
        } catch (Exception $e) {
            throw new Exception("Error al formatear fecha y hora: {$e->getMessage()}");
        }
    }
    
    /**
     * Formatea un texto asegurando que cumpla con las restricciones de longitud
     *
     * @param string $text Texto a formatear
     * @param int $maxLength Longitud máxima permitida
     * @return string Texto formateado
     */
    public function formatText(string $text, int $maxLength): string
    {
        // Eliminar caracteres no permitidos
        $text = $this->removeInvalidChars($text);
        
        // Truncar si excede la longitud máxima
        if (mb_strlen($text) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength);
        }
        
        return $text;
    }
    
    /**
     * Elimina caracteres no permitidos en los textos XML
     *
     * @param string $text Texto a limpiar
     * @return string Texto limpio
     */
    protected function removeInvalidChars(string $text): string
    {
        // Eliminar caracteres de control XML excepto tabulaciones, saltos de línea y retornos de carro
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // Reemplazar caracteres especiales XML
        $text = str_replace(['&', '<', '>', '"', "'"], ['&amp;', '&lt;', '&gt;', '&quot;', '&apos;'], $text);
        
        return $text;
    }
    
    /**
     * Formatea un código numérico con ceros a la izquierda
     *
     * @param string|int $code Código a formatear
     * @param int $length Longitud deseada
     * @return string Código formateado
     */
    public function formatCode($code, int $length): string
    {
        return str_pad((string) $code, $length, '0', STR_PAD_LEFT);
    }
    
    /**
     * Convierte un valor booleano a 'S' o 'N' según el formato requerido por SIFEN
     *
     * @param bool $value Valor booleano
     * @return string 'S' o 'N'
     */
    public function formatBoolean(bool $value): string
    {
        return $value ? 'S' : 'N';
    }
}