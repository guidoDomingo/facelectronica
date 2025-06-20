<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Cliente para interactuar con los servicios SIFEN (Paraguay)
 * 
 * Versión V2 que usa la API Node.js para evitar problemas SOAP/WSDL
 * Resuelve el error "SOAP-ERROR: Parsing WSDL: Couldn't load from SIFEN"
 */
class SifenClientV2
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
        
        Log::info('SifenClientV2 inicializado con API Node.js', [
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
        
        foreach ($posiblesRutas as $ruta) {
            if (file_exists($ruta) && is_readable($ruta)) {
                $this->certificadoRuta = $ruta;
                Log::info('Certificado alternativo encontrado', ['ruta' => $ruta]);
                return true;
            }
        }
        
        Log::warning('No se encontró un certificado válido. Las operaciones que requieran autenticación pueden fallar.');
        return false;
    }
    
    /**
     * Obtiene un cliente SOAP inicializado para un servicio específico
     * 
     * @param string $servicio Nombre del servicio (recepcion, consulta, eventos)
     * @return SoapClient
     * @throws Exception
     */
    private function getSoapClient(string $servicio): SoapClient
    {
        // Si ya tenemos un cliente inicializado, lo devolvemos
        if ($this->soapClients[$servicio] !== null) {
            return $this->soapClients[$servicio];
        }
        
        // Obtenemos la URL del servicio
        $url = $this->wsdlUrls[$this->ambiente][$servicio] ?? null;
        
        if ($url === null) {
            throw new Exception("Servicio SIFEN no válido: $servicio");
        }
        
        try {
            Log::info("Inicializando cliente SOAP para $servicio", ['url' => $url]);
            
            // Configurar contexto SSL
            $sslOptions = [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ];
            
            // Agregar certificado si es válido
            if ($this->certificadoValido) {
                $sslOptions['local_cert'] = $this->certificadoRuta;
                $sslOptions['passphrase'] = $this->certificadoClave;
            }
            
            $context = stream_context_create(['ssl' => $sslOptions]);
            
            // Opciones del cliente SOAP
            $options = [
                'stream_context' => $context,
                'cache_wsdl' => 0,
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 60,
                'soap_version' => 1, // SOAP_1_1
                'keep_alive' => true,
                'user_agent' => 'PHP-SOAP/Laravel',
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS
            ];
            
            // Agregar certificado si es válido
            if ($this->certificadoValido) {
                $options['local_cert'] = $this->certificadoRuta;
                $options['passphrase'] = $this->certificadoClave;
            }
            
            // Inicializar cliente SOAP
            $this->soapClients[$servicio] = new SoapClient($url, $options);
            
            return $this->soapClients[$servicio];
            
        } catch (Exception $e) {
            Log::error("Error inicializando cliente SOAP para $servicio", [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            
            throw new Exception("Error al inicializar cliente SOAP para $servicio: " . $e->getMessage());
        }
    }
    
    /**
     * Consulta el estado de un documento en SIFEN por su CDC
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
                
                Log::info('Consultando estado de documento en SIFEN', [
                    'cdc' => $cdc, 
                    'intento' => $intentos,
                    'ambiente' => $this->ambiente
                ]);
                
                // Obtener cliente SOAP para consultas
                $client = $this->getSoapClient('consulta');
                
                // Preparar parámetros de consulta
                $params = ['dId' => $cdc];
                
                // Ejecutar consulta
                $result = $client->rConsultaDE($params);
                
                // Procesar respuesta
                return $this->procesarRespuestaConsulta($result, $cdc);
                
            } catch (Exception $e) {
                $ultimoError = $e;
                
                Log::warning("Error consultando estado en SIFEN (intento $intentos/$maxIntentos)", [
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
     * Procesa la respuesta de consulta de SIFEN
     * 
     * @param object $response Respuesta original de SIFEN
     * @param string $cdc CDC consultado
     * @return array Respuesta procesada
     */
    private function procesarRespuestaConsulta($response, string $cdc): array
    {
        if (isset($response->rResEnviConsDE)) {
            $respuesta = $response->rResEnviConsDE;
            
            // Extraer información relevante
            $estado = $respuesta->dEstRes ?? 'desconocido';
            $codigo = $respuesta->dCodRes ?? '999';
            $mensaje = $respuesta->dMsgRes ?? 'Sin mensaje';
            $fechaProceso = $respuesta->dFecProc ?? Carbon::now()->toIso8601String();
            
            return [
                'success' => true,
                'cdc' => $cdc,
                'estado' => $estado,
                'codigo' => $codigo,
                'mensaje' => $mensaje,
                'fechaProceso' => $fechaProceso,
                'detalles' => $respuesta
            ];
        }
        
        return [
            'success' => false,
            'cdc' => $cdc,
            'mensaje' => 'Respuesta de SIFEN no válida',
            'estado' => 'error',
            'codigo' => '999'
        ];
    }
    
    /**
     * Envía un documento XML a SIFEN
     * 
     * @param string $xml XML del documento a enviar
     * @param int $maxIntentos Número máximo de intentos
     * @return array Respuesta procesada
     * @throws Exception
     */
    public function enviarDocumento(string $xml, int $maxIntentos = 3): array
    {
        $intentos = 0;
        $ultimoError = null;
        $cdc = $this->extraerCDC($xml);
        
        while ($intentos < $maxIntentos) {
            try {
                $intentos++;
                
                Log::info('Enviando documento a SIFEN', [
                    'cdc' => $cdc, 
                    'intento' => $intentos,
                    'tamaño_xml' => strlen($xml),
                ]);
                
                // Obtener cliente SOAP para envíos
                $client = $this->getSoapClient('recepcion');
                
                // Preparar datos para envío
                $params = [
                    'rEnviDe' => [
                        'dDVId' => $xml
                    ]
                ];
                
                // Ejecutar envío
                $result = $client->rEnviDe($params);
                
                // Procesar respuesta
                return $this->procesarRespuestaEnvio($result, $cdc);
                
            } catch (Exception $e) {
                $ultimoError = $e;
                
                Log::warning("Error enviando documento a SIFEN (intento $intentos/$maxIntentos)", [
                    'cdc' => $cdc,
                    'error' => $e->getMessage()
                ]);
                
                // Esperar antes de reintentar
                if ($intentos < $maxIntentos) {
                    $tiempoEspera = pow(2, $intentos) * 500;
                    usleep($tiempoEspera * 1000);
                }
            }
        }
        
        Log::error('Falló el envío de documento después de múltiples intentos', [
            'cdc' => $cdc, 
            'intentos' => $maxIntentos,
            'ultimo_error' => $ultimoError ? $ultimoError->getMessage() : null
        ]);
        
        // Respuesta de error después de agotar intentos
        return [
            'success' => false,
            'cdc' => $cdc,
            'error' => 'MAX_RETRIES_EXCEEDED',
            'message' => $ultimoError ? $ultimoError->getMessage() : 'Error desconocido',
            'codigo' => '999',
            'estado' => 'error'
        ];
    }
    
    /**
     * Procesa la respuesta de envío de SIFEN
     * 
     * @param object $response Respuesta original
     * @param string $cdc CDC del documento
     * @return array Respuesta procesada
     */
    private function procesarRespuestaEnvio($response, $cdc): array
    {
        if (isset($response->rRetEnviDe)) {
            $respuesta = $response->rRetEnviDe;
            
            // Extraer información relevante
            $estado = $respuesta->dEstRes ?? 'desconocido';
            $codigo = $respuesta->dCodRes ?? '999';
            $mensaje = $respuesta->dMsgRes ?? 'Sin mensaje';
            $fechaProceso = $respuesta->dFecProc ?? Carbon::now()->toIso8601String();
            
            return [
                'success' => true,
                'cdc' => $cdc,
                'estado' => $estado,
                'codigo' => $codigo,
                'mensaje' => $mensaje,
                'fechaProceso' => $fechaProceso,
                'detalles' => $respuesta
            ];
        }
        
        return [
            'success' => false,
            'cdc' => $cdc,
            'mensaje' => 'Respuesta de envío no válida',
            'estado' => 'error',
            'codigo' => '999'
        ];
    }
    
    /**
     * Extrae el CDC de un XML
     * 
     * @param string $xml XML del documento
     * @return string|null CDC extraído o null si no se encuentra
     */
    private function extraerCDC(string $xml): ?string
    {
        if (preg_match('/<dId>(.*?)<\/dId>/', $xml, $matches)) {
            return $matches[1];
        }
        
        // Si no se encuentra el CDC, generar un ID aleatorio para seguimiento
        return 'UNKNOWN-' . substr(md5(uniqid()), 0, 8);
    }
    
    /**
     * Verifica la conectividad con SIFEN
     * 
     * @return array Resultados de diagnóstico
     */
    public function verificarConectividad(): array
    {
        $resultados = [];
        
        foreach (['recepcion', 'consulta', 'eventos'] as $servicio) {
            $url = $this->wsdlUrls[$this->ambiente][$servicio];
            $resultados[$servicio] = [];
            
            try {
                // Verificar acceso al WSDL
                $headers = @get_headers($url);
                $resultados[$servicio]['accesible'] = $headers !== false;
                $resultados[$servicio]['headers'] = $headers;
                
                // Intentar crear cliente SOAP
                $this->getSoapClient($servicio);
                $resultados[$servicio]['soap_ok'] = true;
                $resultados[$servicio]['mensaje'] = 'Conectividad OK';
                
            } catch (Exception $e) {
                $resultados[$servicio]['soap_ok'] = false;
                $resultados[$servicio]['error'] = $e->getMessage();
                $resultados[$servicio]['mensaje'] = 'Error conectando a ' . $servicio;
            }
        }
        
        return [
            'ambiente' => $this->ambiente,
            'certificado_valido' => $this->certificadoValido,
            'certificado_ruta' => $this->certificadoRuta,
            'servicios' => $resultados
        ];
    }
}
