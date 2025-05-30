<?php

namespace App\Services\FacturacionElectronica;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Servicio para gestionar certificados digitales para SIFEN
 */
class CertificadoDigitalService
{
    /**
     * Verifica si el certificado digital existe y es válido
     *
     * @param string|null $rutaCertificado Ruta al archivo del certificado (opcional, usa la configuración por defecto si no se proporciona)
     * @param string|null $clave Contraseña del certificado (opcional, usa la configuración por defecto si no se proporciona)
     * @return bool Si el certificado es válido
     */
    public function verificarCertificado(?string $rutaCertificado = null, ?string $clave = null): bool
    {
        // Usar valores predeterminados si no se proporcionan
        $rutaCertificado = $rutaCertificado ?? config('facturacion_electronica.firma_digital.ruta_certificado');
        $clave = $clave ?? config('facturacion_electronica.firma_digital.clave_certificado');

        // Verificar que exista el archivo
        if (!file_exists($rutaCertificado)) {
            Log::error("Certificado digital no encontrado en la ruta: {$rutaCertificado}");
            return false;
        }

        // Intentar leer el certificado para verificar su validez
        try {
            // Cargar el certificado PKCS#12
            $cert = file_get_contents($rutaCertificado);
            if ($cert === false) {
                Log::error("No se pudo leer el archivo de certificado: {$rutaCertificado}");
                return false;
            }

            // Intentar leer la información del certificado con la clave proporcionada
            $certInfo = [];
            $result = openssl_pkcs12_read($cert, $certInfo, $clave);
            if (!$result) {
                Log::error("No se pudo leer el certificado PKCS#12. Verifique la contraseña.");
                return false;
            }

            // Verificar que contenga un certificado y una clave privada
            if (!isset($certInfo['cert']) || !isset($certInfo['pkey'])) {
                Log::error("El archivo PKCS#12 no contiene un certificado y una clave privada válidos.");
                return false;
            }

            // Extraer información del certificado para validarla
            $x509 = openssl_x509_read($certInfo['cert']);
            if ($x509 === false) {
                Log::error("No se pudo leer el certificado X509.");
                return false;
            }

            // Verificar fechas de validez
            $certData = openssl_x509_parse($x509);
            $now = time();
            
            if ($certData['validFrom_time_t'] > $now) {
                Log::error("El certificado aún no es válido. Será válido a partir de: " . 
                    date('Y-m-d H:i:s', $certData['validFrom_time_t']));
                return false;
            }

            if ($certData['validTo_time_t'] < $now) {
                Log::error("El certificado ha expirado. Expiró el: " . 
                    date('Y-m-d H:i:s', $certData['validTo_time_t']));
                return false;
            }

            // Si llegamos hasta aquí, el certificado es válido
            Log::info("Certificado digital válido. Válido hasta: " . date('Y-m-d H:i:s', $certData['validTo_time_t']));
            return true;

        } catch (Exception $e) {
            Log::error("Error al verificar el certificado digital: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información del certificado digital
     *
     * @param string|null $rutaCertificado Ruta al archivo del certificado
     * @param string|null $clave Contraseña del certificado
     * @return array|null Información del certificado o null si hay error
     */
    public function obtenerInfoCertificado(?string $rutaCertificado = null, ?string $clave = null): ?array
    {
        // Usar valores predeterminados si no se proporcionan
        $rutaCertificado = $rutaCertificado ?? config('facturacion_electronica.firma_digital.ruta_certificado');
        $clave = $clave ?? config('facturacion_electronica.firma_digital.clave_certificado');

        try {
            // Verificar que existe y es válido
            if (!$this->verificarCertificado($rutaCertificado, $clave)) {
                return null;
            }

            // Cargar el certificado
            $cert = file_get_contents($rutaCertificado);
            $certInfo = [];
            openssl_pkcs12_read($cert, $certInfo, $clave);

            // Extraer información
            $x509 = openssl_x509_read($certInfo['cert']);
            $certData = openssl_x509_parse($x509);

            // Preparar información útil
            return [
                'subject' => $this->formatDN($certData['subject']),
                'issuer' => $this->formatDN($certData['issuer']),
                'valid_from' => date('Y-m-d H:i:s', $certData['validFrom_time_t']),
                'valid_to' => date('Y-m-d H:i:s', $certData['validTo_time_t']),
                'days_remaining' => ceil(($certData['validTo_time_t'] - time()) / 86400),
                'serial_number' => $certData['serialNumber'] ?? 'Desconocido',
                'fingerprint' => $this->getFingerprint($certInfo['cert']),
            ];
        } catch (Exception $e) {
            Log::error("Error al obtener información del certificado: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Importa un nuevo certificado digital
     *
     * @param string $rutaArchivo Ruta temporal al archivo del certificado
     * @param string $clave Contraseña del certificado
     * @param string|null $nombreDestino Nombre para guardar el archivo (opcional)
     * @return bool Si la importación fue exitosa
     */
    public function importarCertificado(string $rutaArchivo, string $clave, ?string $nombreDestino = null): bool
    {
        try {
            // Verificar que el archivo existe
            if (!file_exists($rutaArchivo)) {
                Log::error("Archivo de certificado no encontrado: {$rutaArchivo}");
                return false;
            }

            // Verificar que el certificado es válido
            $cert = file_get_contents($rutaArchivo);
            $certInfo = [];
            if (!openssl_pkcs12_read($cert, $certInfo, $clave)) {
                Log::error("No se pudo leer el certificado PKCS#12. La contraseña podría ser incorrecta.");
                return false;
            }

            // Establecer un nombre de destino si no se proporcionó
            if (!$nombreDestino) {
                $x509 = openssl_x509_read($certInfo['cert']);
                $certData = openssl_x509_parse($x509);
                $cn = $certData['subject']['CN'] ?? 'certificado';
                $nombreDestino = Str::slug($cn) . '.p12';
            }

            // Asegurar que tiene extensión .p12
            if (!Str::endsWith($nombreDestino, '.p12')) {
                $nombreDestino .= '.p12';
            }

            // Ruta de destino
            $directorio = storage_path('app/certificados');
            if (!File::isDirectory($directorio)) {
                File::makeDirectory($directorio, 0755, true);
            }
            
            $rutaDestino = $directorio . '/' . $nombreDestino;

            // Copiar el archivo
            if (file_exists($rutaDestino)) {
                // Hacer una copia de respaldo antes de sobrescribir
                $backup = $rutaDestino . '.bak.' . date('YmdHis');
                File::copy($rutaDestino, $backup);
            }

            // Copiar el certificado a la ubicación de destino
            File::copy($rutaArchivo, $rutaDestino);

            // Actualizar la configuración si es necesario
            if (config('facturacion_electronica.firma_digital.ruta_certificado') === storage_path('app/certificados/certificado.p12')) {
                // Esto es solo una sugerencia para actualizar la configuración
                Log::info("Considere actualizar la configuración en el archivo .env: FACTURACION_CERT_PATH={$rutaDestino}");
            }

            Log::info("Certificado importado exitosamente: {$rutaDestino}");
            return true;
        } catch (Exception $e) {
            Log::error("Error al importar el certificado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Formatea un Distinguished Name (DN) para mostrarlo de forma más legible
     *
     * @param array $dn Array con el DN
     * @return string DN formateado
     */
    private function formatDN(array $dn): string
    {
        $parts = [];
        foreach ($dn as $key => $value) {
            $parts[] = "{$key}={$value}";
        }
        return implode(', ', $parts);
    }

    /**
     * Obtiene la huella digital del certificado
     *
     * @param string $cert Certificado en formato PEM
     * @return string Huella digital
     */
    private function getFingerprint(string $cert): string
    {
        $fingerprint = '';
        
        try {
            $der = openssl_x509_read($cert);
            $data = '';
            openssl_x509_export($der, $data);
            $fingerprint = openssl_x509_fingerprint($der, 'sha256');
        } catch (Exception $e) {
            Log::error("Error al calcular huella digital: " . $e->getMessage());
            $fingerprint = 'Error al calcular huella digital';
        }

        return $fingerprint;
    }
}
