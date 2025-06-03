# Guía de Solución de Problemas - SIFEN

Este documento detalla los pasos para solucionar problemas comunes al conectar con SIFEN (Sistema Integrado de Facturación Electrónica Nacional de Paraguay) desde PHP.

## Diagnóstico de Problemas

### Verificar la implementación SOAP

El primer paso es verificar que se esté utilizando la versión correcta del cliente SIFEN:

```php
// Usar la versión mejorada y más robusta del cliente
use App\Services\SifenClientV2;

$client = new SifenClientV2();
```

### Problemas comunes y soluciones

#### 1. Certificado no encontrado o inaccesible

**Síntoma**: Error "El certificado no es válido o no está accesible"

**Solución**:
- Ejecutar `php reset_sifen_config.php` para regenerar o reutilizar un certificado válido
- Verificar que la ruta del certificado en `config/facturacion_electronica.php` sea correcta
- Comprobar permisos de lectura en el certificado

#### 2. Error de SOAP WSDL

**Síntoma**: Error "SOAP-ERROR: Parsing WSDL: Couldn't load from..."

**Solución**:
- Verificar conectividad con `php test_sifen_v2.php`
- Limpiar cache WSDL ejecutando `php reset_sifen_config.php`
- Comprobar que las URLs de SIFEN estén accesibles desde el servidor

#### 3. Error de constantes SOAP

**Síntoma**: Errores como "Undefined constant SOAP_1_1" o "WSDL_CACHE_NONE"

**Solución**:
- Verificar que la extensión SOAP esté instalada: `php -m | grep soap`
- Usar valores numéricos en lugar de constantes: `'soap_version' => 1` en vez de `SOAP_1_1`

## Herramientas de diagnóstico

El proyecto incluye varias herramientas para diagnosticar problemas:

- `test_sifen_v2.php` - Prueba completa de conectividad con diagnósticos
- `test_sifen_simple.php` - Prueba simplificada de consulta CDC
- `reset_sifen_config.php` - Resetea la configuración y regenera certificados
- `test_sifen_connection.php` - Diagnóstico detallado de conexión

## Configuración del certificado

Los certificados deben estar en formato PKCS12 (extensión .p12) y configurarse en:

```php
// config/facturacion_electronica.php
'firma_digital' => [
    'ruta_certificado' => env('FACTURACION_CERT_PATH', storage_path('app/certificados/certificado.p12')),
    'clave_certificado' => env('FACTURACION_CERT_CLAVE', 'clave_certificado'),
    'habilitada' => env('FACTURACION_FIRMA_HABILITADA', false),
],
```

## URLs de SIFEN

Las URLs correctas para los servicios SIFEN son:

### Ambiente de pruebas
- Recepción: `https://sifen-test.set.gov.py/de/ws/sync-services.wsdl`
- Consultas: `https://sifen-test.set.gov.py/de/ws/consultas-services.wsdl`
- Eventos: `https://sifen-test.set.gov.py/de/ws/eventos-services.wsdl`

### Ambiente de producción
- Recepción: `https://sifen.set.gov.py/de/ws/sync-services.wsdl`
- Consultas: `https://sifen.set.gov.py/de/ws/consultas-services.wsdl`
- Eventos: `https://sifen.set.gov.py/de/ws/eventos-services.wsdl`

## Requisitos del sistema

- PHP 7.4 o superior
- Extensiones: soap, openssl, curl
- Certificado digital válido (.p12)

## Notas importantes

- Para producción, debe obtener un certificado digital VÁLIDO emitido por una entidad certificadora autorizada
- Los certificados generados por los scripts de este proyecto son SOLO PARA PRUEBAS
- La versión mejorada del cliente (`SifenClientV2`) incluye reintentos automáticos y mejor manejo de errores
