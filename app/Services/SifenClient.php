<?php

namespace App\Services;

use SoapClient;
use SoapHeader;
use Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SifenClient
{    private $wsdlUrls = [
        'test' => 'https://sifen-test.set.gov.py/de/ws/sync-services.wsdl',
        'prod' => 'https://sifen.set.gov.py/de/ws/sync-services.wsdl'
    ];
    
    private $consulta_wsdlUrls = [
        'test' => 'https://sifen-test.set.gov.py/de/ws/consultas-services.wsdl',
        'prod' => 'https://sifen.set.gov.py/de/ws/consultas-services.wsdl'
    ];

    private $soapClient;
    private $ambiente;
    private $certificadoRuta;
    private $certificadoClave;    public function __construct()
    {
        $this->ambiente = config('facturacion_electronica.ambiente', 'test');
        $this->certificadoRuta = config('facturacion_electronica.firma_digital.ruta_certificado');
        $this->certificadoClave = config('facturacion_electronica.firma_digital.clave_certificado');
        
        // Si no tenemos certificado configurado, intentar usar uno de prueba del node-service
        if (empty($this->certificadoRuta) || !file_exists($this->certificadoRuta)) {
            $possibleCerts = [
                base_path('node-service/certificado.p12'),
                storage_path('app/certificados/certificado.p12'),
                base_path('certificado.p12')
            ];
            
            foreach ($possibleCerts as $certPath) {
                if (file_exists($certPath)) {
                    $this->certificadoRuta = $certPath;
                    Log::info('Usando certificado alternativo', ['ruta' => $certPath]);
                    break;
                }
            }
        }
        
        Log::info('Inicializando cliente SIFEN', [
            'ambiente' => $this->ambiente,
            'certificado' => $this->certificadoRuta,
            'certificado_existe' => file_exists($this->certificadoRuta),
            'url' => $this->wsdlUrls[$this->ambiente]
        ]);
        
        $this->initializeSoapClient();
    }
    
    /**
     * Verifica si el certificado existe y es accesible
     *
     * @return bool
     */    private function verificarCertificado(): bool
    {
        if (empty($this->certificadoRuta)) {
            Log::error('Ruta de certificado no configurada');
            return false;
        }
        
        // Intentar con la ruta configurada
        if (file_exists($this->certificadoRuta) && is_readable($this->certificadoRuta)) {
            Log::info('Certificado verificado correctamente', ['ruta' => $this->certificadoRuta]);
            return true;
        }
        
        // Si el certificado no está en la ruta configurada, buscar en node-service
        $nodeCertPath = base_path('node-service/certificado.p12');
        if (file_exists($nodeCertPath) && is_readable($nodeCertPath)) {
            Log::info('Usando certificado de node-service como alternativa', ['ruta' => $nodeCertPath]);
            $this->certificadoRuta = $nodeCertPath;
            return true;
        }
        
        // Verificar si existe el directorio
        $certDir = dirname($this->certificadoRuta);
        if (!file_exists($certDir)) {
            Log::error('El directorio de certificados no existe', ['dir' => $certDir]);
            
            // Intentar crear el directorio
            try {
                mkdir($certDir, 0755, true);
                Log::info('Directorio de certificados creado', ['dir' => $certDir]);
            } catch (\Exception $e) {
                Log::error('No se pudo crear el directorio de certificados', ['error' => $e->getMessage()]);
            }
        }
        
        Log::error('No se encontró un certificado válido', [
            'ruta_configurada' => $this->certificadoRuta,
            'ruta_alternativa' => $nodeCertPath,
            'existe_ruta_config' => file_exists($this->certificadoRuta) ? 'Sí' : 'No',
            'es_legible_config' => is_readable($this->certificadoRuta) ? 'Sí' : 'No'
        ]);
        
        return false;
    }    private function initializeSoapClient()
    {
        try {
            // Verificar certificado antes de continuar
            $certificadoValido = $this->verificarCertificado();
            
            // Si estamos en modo test y no tenemos certificado, intentamos modo sin certificado
            if (!$certificadoValido && $this->ambiente === 'test') {
                Log::warning('Iniciando en modo TEST sin certificado. Algunas operaciones pueden fallar.');
            } else if (!$certificadoValido) {
                throw new Exception("El certificado no es válido o no está accesible");
            }            // Crear contexto SSL
            Log::info('Inicializando contexto SSL', [
                'certificado_ruta' => $this->certificadoRuta,
                'ambiente' => $this->ambiente,
                'certificado_valido' => $certificadoValido ? 'Sí' : 'No'
            ]);
            
            $sslOptions = [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLS_CLIENT,
            ];
            
            // Agregar certificado solo si es válido
            if ($certificadoValido) {
                $sslOptions['local_cert'] = $this->certificadoRuta;
                $sslOptions['passphrase'] = $this->certificadoClave;
            }
            
            $context = stream_context_create([
                'ssl' => $sslOptions
            ]);            // Configurar opciones del cliente SOAP
            $options = [
                'stream_context' => $context,
                'cache_wsdl' => 0, // WSDL_CACHE_NONE = 0
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 60, // Mayor timeout para mayor tolerancia
                'soap_version' => 1, // SOAP_1_1 = 1
                'ssl_method' => 'TLS', // Forzar TLS
                'keep_alive' => true,
                'user_agent' => 'PHP-SOAP/Laravel',
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS
            ];
            
            // Agregar certificado solo si es válido
            if ($certificadoValido) {
                $options['local_cert'] = $this->certificadoRuta;
                $options['passphrase'] = $this->certificadoClave;
            }$this->soapClient = new \SoapClient(
                $this->wsdlUrls[$this->ambiente],
                $options
            );

            Log::info('Cliente SOAP inicializado correctamente', [
                'ambiente' => $this->ambiente
            ]);        } catch (Exception $e) {
            Log::error('Error inicializando cliente SOAP: ' . $e->getMessage(), [
                'url' => $this->wsdlUrls[$this->ambiente],
                'certificado' => $this->certificadoRuta,
                'ambiente' => $this->ambiente
            ]);
            
            // Verificar conectividad básica antes de fallar
            try {
                $headers = get_headers($this->wsdlUrls[$this->ambiente]);
                Log::info('Headers de respuesta WSDL:', ['headers' => $headers]);
            } catch (Exception $connEx) {
                Log::error('Error de conectividad básica: ' . $connEx->getMessage());
            }
            
            throw new Exception('Error al inicializar conexión con SIFEN: ' . $e->getMessage());
        }
    }

    /**
     * Consulta el estado de un documento en SIFEN
     *
     * @param string $cdc CDC del documento a consultar
     * @return array Respuesta de SIFEN
     * @throws Exception
     */
    public function consultarEstadoDocumento(string $cdc): array
    {
        try {
            Log::info('Consultando estado de documento en SIFEN', ['cdc' => $cdc]);

            $params = [
                'dId' => $cdc
            ];

            $result = $this->soapClient->rConsultaDE($params);
            
            Log::info('Respuesta de SIFEN recibida', [
                'cdc' => $cdc,
                'respuesta' => $result
            ]);

            return $this->procesarRespuestaConsulta($result);
        } catch (Exception $e) {
            Log::error('Error consultando estado en SIFEN: ' . $e->getMessage(), [
                'cdc' => $cdc
            ]);
            throw new Exception('Error consultando estado en SIFEN: ' . $e->getMessage());
        }
    }

    /**
     * Procesa la respuesta de consulta de SIFEN
     *
     * @param object $response
     * @return array
     */
    private function procesarRespuestaConsulta($response): array
    {
        $estado = 'error';
        $codigo = '999';
        $mensaje = 'Error desconocido';
        $fechaProceso = Carbon::now()->toIso8601String();

        if (isset($response->rResEnviConsDE)) {
            $respuesta = $response->rResEnviConsDE;
            
            // Extraer información relevante
            $estado = $respuesta->dEstRes ?? 'desconocido';
            $codigo = $respuesta->dCodRes ?? '999';
            $mensaje = $respuesta->dMsgRes ?? 'Sin mensaje';
            $fechaProceso = $respuesta->dFecProc ?? Carbon::now()->toIso8601String();
        }

        return [
            'success' => true,
            'resultado' => [
                'estado' => 'real',
                'respuesta' => [
                    'estado' => $estado,
                    'codigo' => $codigo,
                    'mensaje' => $mensaje,
                    'fechaProceso' => $fechaProceso
                ]
            ]
        ];
    }

    /**
     * Envía un documento a SIFEN
     *
     * @param string $xml XML del documento a enviar
     * @return array Respuesta de SIFEN
     * @throws Exception
     */
    public function enviarDocumento(string $xml): array
    {
        try {
            Log::info('Enviando documento a SIFEN', [
                'tamaño_xml' => strlen($xml)
            ]);

            $params = [
                'dDatGral' => [
                    'dId' => $this->extraerCDC($xml),
                ],
                'dDatRec' => $xml
            ];

            $result = $this->soapClient->rEnviDe($params);
            
            Log::info('Respuesta de envío recibida', [
                'respuesta' => $result
            ]);

            return $this->procesarRespuestaEnvio($result);
        } catch (Exception $e) {
            Log::error('Error enviando documento a SIFEN: ' . $e->getMessage());
            throw new Exception('Error al enviar documento a SIFEN: ' . $e->getMessage());
        }
    }

    /**
     * Procesa la respuesta de envío de SIFEN
     *
     * @param object $response
     * @return array
     */
    private function procesarRespuestaEnvio($response): array
    {
        if (isset($response->rRetEnviDe)) {
            $respuesta = $response->rRetEnviDe;
            
            return [
                'success' => true,
                'resultado' => [
                    'estado' => 'real',
                    'recepcion' => [
                        'codigo' => $respuesta->dCodRes ?? '999',
                        'mensaje' => $respuesta->dMsgRes ?? 'Sin mensaje',
                        'estado' => $respuesta->dEstRes ?? 'desconocido',
                        'fechaProceso' => $respuesta->dFecProc ?? Carbon::now()->toIso8601String()
                    ]
                ]
            ];
        }

        return [
            'success' => false,
            'message' => 'Respuesta de SIFEN no válida'
        ];
    }    /**
     * Extrae el CDC del XML
     *
     * @param string $xml
     * @return string|null
     */
    private function extraerCDC(string $xml): ?string
    {
        if (preg_match('/<dId>(.*?)<\/dId>/', $xml, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * Prueba la conexión a SIFEN
     *
     * @return array Resultado del diagnóstico
     */
    public function testConexion(): array
    {
        $resultados = [
            'certificado' => [
                'existe' => false,
                'accesible' => false,
                'mensaje' => ''
            ],
            'conectividad' => [
                'ok' => false,
                'mensaje' => '',
                'headers' => []
            ],
            'soap' => [
                'ok' => false,
                'mensaje' => '',
                'functions' => []
            ]
        ];
        
        // Probar certificado
        try {
            if (file_exists($this->certificadoRuta)) {
                $resultados['certificado']['existe'] = true;
                
                if (is_readable($this->certificadoRuta)) {
                    $resultados['certificado']['accesible'] = true;
                    $resultados['certificado']['mensaje'] = 'Certificado accesible';
                } else {
                    $resultados['certificado']['mensaje'] = 'Certificado existe pero no es legible';
                }
            } else {
                $resultados['certificado']['mensaje'] = 'Certificado no encontrado';
            }
        } catch (Exception $e) {
            $resultados['certificado']['mensaje'] = 'Error al verificar certificado: ' . $e->getMessage();
        }
        
        // Probar conectividad
        try {
            $headers = @get_headers($this->wsdlUrls[$this->ambiente]);
            if ($headers) {
                $resultados['conectividad']['ok'] = true;
                $resultados['conectividad']['mensaje'] = 'Conectividad OK';
                $resultados['conectividad']['headers'] = $headers;
            } else {
                $resultados['conectividad']['mensaje'] = 'No se pudo conectar al servidor SIFEN';
            }
        } catch (Exception $e) {
            $resultados['conectividad']['mensaje'] = 'Error de conectividad: ' . $e->getMessage();
        }
        
        // Probar SOAP
        try {
            if ($this->soapClient) {
                $resultados['soap']['ok'] = true;
                $resultados['soap']['mensaje'] = 'Cliente SOAP inicializado correctamente';
                $resultados['soap']['functions'] = $this->soapClient->__getFunctions();
            } else {
                $resultados['soap']['mensaje'] = 'Cliente SOAP no inicializado';
            }
        } catch (Exception $e) {
            $resultados['soap']['mensaje'] = 'Error en cliente SOAP: ' . $e->getMessage();
        }
        
        return $resultados;
    }
}
