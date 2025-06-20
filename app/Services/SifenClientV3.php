<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Cliente para interactuar con los servicios SIFEN (Paraguay)
 * 
 * Versión V3 que usa la API Node.js para evitar problemas SOAP/WSDL
 * Resuelve el error "SOAP-ERROR: Parsing WSDL: Couldn't load from SIFEN"
 */
class SifenClientV3
{
    /**
     * URL base de la API Node.js
     */
    private $nodeApiUrl;

    /**
     * Ambiente SIFEN (test o prod)
     */
    private $ambiente;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ambiente = config('facturacion_electronica.ambiente', 'test');
        $this->nodeApiUrl = config('facturacion_electronica.node_api_url', 'http://localhost:3000');
        
        Log::info('SifenClientV3 inicializado con API Node.js', [
            'ambiente' => $this->ambiente,
            'node_api_url' => $this->nodeApiUrl
        ]);
    }

    /**
     * Consulta el estado de un documento electrónico en SIFEN
     * 
     * @param string $cdc CDC del documento a consultar
     * @param int $maxIntentos Número máximo de intentos de conexión
     * @return array Respuesta procesada de SIFEN
     * @throws Exception
     */
    public function consultarEstadoDocumento(string $cdc, int $maxIntentos = 3): array
    {
        $intentos = 0;
        $ultimoError = null;
        
        while ($intentos < $maxIntentos) {
            try {
                $intentos++;
                
                Log::info('Consultando estado de documento usando API Node.js', [
                    'cdc' => $cdc, 
                    'intento' => $intentos,
                    'ambiente' => $this->ambiente,
                    'url' => $this->nodeApiUrl . '/sifen/consultar-estado'
                ]);
                
                // Llamar a la API Node.js
                $response = Http::timeout(30)->post($this->nodeApiUrl . '/sifen/consultar-estado', [
                    'cdc' => $cdc,
                    'ambiente' => $this->ambiente
                ]);
                
                if ($response->successful()) {
                    $responseData = $response->json();
                    
                    if (isset($responseData['success']) && $responseData['success']) {
                        Log::info('Consulta exitosa usando API Node.js', [
                            'cdc' => $cdc,
                            'resultado' => $responseData
                        ]);
                        
                        return $responseData;
                    } else {
                        $error = $responseData['message'] ?? 'Error desconocido en API Node.js';
                        throw new Exception($error);
                    }
                } else {
                    throw new Exception('Error HTTP al consultar API Node.js: ' . $response->status());
                }
                
            } catch (Exception $e) {
                $ultimoError = $e;
                
                Log::warning("Error consultando estado usando API Node.js (intento $intentos/$maxIntentos)", [
                    'cdc' => $cdc,
                    'error' => $e->getMessage()
                ]);
                
                // Esperar antes de reintentar (backoff exponencial)
                if ($intentos < $maxIntentos) {
                    $tiempoEspera = pow(2, $intentos) * 500; // 1s, 2s, 4s...
                    usleep($tiempoEspera * 1000);
                }
            }
        }
        
        Log::error('Falló la consulta de estado después de múltiples intentos', [
            'cdc' => $cdc, 
            'intentos' => $maxIntentos,
            'ultimo_error' => $ultimoError ? $ultimoError->getMessage() : null
        ]);
        
        // Respuesta de error después de agotar intentos
        return [
            'success' => false,
            'error' => 'MAX_RETRIES_EXCEEDED',
            'message' => $ultimoError ? $ultimoError->getMessage() : 'Error desconocido',
            'codigo' => '999',
            'estado' => 'error'
        ];
    }

    /**
     * Envía un documento electrónico a SIFEN
     * 
     * @param string $xml XML del documento a enviar
     * @param int $maxIntentos Número máximo de intentos de conexión
     * @return array Respuesta procesada de SIFEN
     * @throws Exception
     */
    public function enviarDocumento(string $xml, int $maxIntentos = 3): array
    {
        $intentos = 0;
        $ultimoError = null;
        
        while ($intentos < $maxIntentos) {
            try {
                $intentos++;
                
                Log::info('Enviando documento usando API Node.js', [
                    'tamaño_xml' => strlen($xml),
                    'intento' => $intentos,
                    'ambiente' => $this->ambiente,
                    'url' => $this->nodeApiUrl . '/sifen/enviar-documento'
                ]);
                
                // Llamar a la API Node.js
                $response = Http::timeout(60)->post($this->nodeApiUrl . '/sifen/enviar-documento', [
                    'xml' => $xml,
                    'ambiente' => $this->ambiente
                ]);
                
                if ($response->successful()) {
                    $responseData = $response->json();
                    
                    if (isset($responseData['success']) && $responseData['success']) {
                        Log::info('Envío exitoso usando API Node.js', [
                            'resultado' => $responseData
                        ]);
                        
                        return $responseData;
                    } else {
                        $error = $responseData['message'] ?? 'Error desconocido en API Node.js';
                        throw new Exception($error);
                    }
                } else {
                    throw new Exception('Error HTTP al enviar documento via API Node.js: ' . $response->status());
                }
                
            } catch (Exception $e) {
                $ultimoError = $e;
                
                Log::warning("Error enviando documento usando API Node.js (intento $intentos/$maxIntentos)", [
                    'error' => $e->getMessage()
                ]);
                
                // Esperar antes de reintentar (backoff exponencial)
                if ($intentos < $maxIntentos) {
                    $tiempoEspera = pow(2, $intentos) * 500; // 1s, 2s, 4s...
                    usleep($tiempoEspera * 1000);
                }
            }
        }
        
        Log::error('Falló el envío de documento después de múltiples intentos', [
            'intentos' => $maxIntentos,
            'ultimo_error' => $ultimoError ? $ultimoError->getMessage() : null
        ]);
        
        // Respuesta de error después de agotar intentos
        return [
            'success' => false,
            'error' => 'MAX_RETRIES_EXCEEDED',
            'message' => $ultimoError ? $ultimoError->getMessage() : 'Error desconocido',
            'codigo' => '999',
            'estado' => 'error'
        ];
    }

    /**
     * Verifica la conectividad con la API Node.js
     * 
     * @return array Resultado del diagnóstico
     */
    public function testConexion(): array
    {
        $resultados = [
            'node_api' => [
                'ok' => false,
                'mensaje' => '',
                'url' => $this->nodeApiUrl
            ],
            'ambiente' => $this->ambiente,
            'configuracion' => [
                'node_api_url' => $this->nodeApiUrl
            ]
        ];
        
        try {
            Log::info('Probando conectividad con API Node.js', [
                'url' => $this->nodeApiUrl
            ]);
            
            // Probar endpoint de salud si existe
            $response = Http::timeout(10)->get($this->nodeApiUrl . '/health');
            
            if ($response->successful()) {
                $resultados['node_api']['ok'] = true;
                $resultados['node_api']['mensaje'] = 'API Node.js accesible';
                $resultados['node_api']['respuesta'] = $response->json();
            } else {
                $resultados['node_api']['mensaje'] = 'API Node.js respondió con error: ' . $response->status();
            }
        } catch (Exception $e) {
            $resultados['node_api']['mensaje'] = 'Error al conectar con API Node.js: ' . $e->getMessage();
            
            Log::error('Error probando conectividad con API Node.js', [
                'error' => $e->getMessage(),
                'url' => $this->nodeApiUrl
            ]);
        }
        
        return $resultados;
    }
}
