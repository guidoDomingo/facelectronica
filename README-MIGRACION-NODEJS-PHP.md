# Migración de Facturación Electrónica de Node.js a PHP Nativo

## Introducción

Este documento describe la migración de la implementación de facturación electrónica para SIFEN (Paraguay) desde la dependencia de Node.js con `facturacionelectronicapy-xmlgen` a una implementación nativa en PHP. Esta migración elimina la necesidad de mantener un servicio Node.js separado, simplificando la arquitectura y mejorando la integración con Laravel.

## Motivación

- **Simplificación de la arquitectura**: Eliminar la dependencia de un servicio Node.js separado.
- **Mejor integración con Laravel**: Implementación completamente nativa en PHP.
- **Reducción de dependencias**: Menos componentes para mantener y actualizar.
- **Mejora en el rendimiento**: Eliminación de la comunicación HTTP entre servicios.

## Estructura de la Implementación

La nueva implementación consta de los siguientes componentes:

### 1. Servicio Principal

`App\Services\FacturacionElectronica\FacturacionElectronicaServiceV2`

Este servicio reemplaza al anterior que dependía de Node.js y proporciona los mismos métodos pero con implementación nativa en PHP:

- `generateXML()`: Genera XML para documentos electrónicos
- `generateXMLEventoCancelacion()`: Genera XML para eventos de cancelación
- `generateXMLEventoInutilizacion()`: Genera XML para eventos de inutilización
- `generateCDC()`: Genera códigos CDC
- `validateData()`: Valida datos según reglas de SIFEN

### 2. Generador de XML

`App\Services\FacturacionElectronica\XmlGenerator\XmlGeneratorService`

Implementa la lógica principal para generar documentos XML según las especificaciones de SIFEN:

- Creación de estructura XML base
- Adición de datos del emisor, receptor, ítems, etc.
- Formateo del XML final

### 3. Clases Auxiliares

- `CdcGenerator`: Genera y valida códigos CDC
- `DataFormatter`: Formatea datos según requisitos de SIFEN
- `XmlValidator`: Valida datos según reglas de SIFEN

### 4. Controlador de API

`App\Http\Controllers\Api\FacturacionElectronicaController`

Expone endpoints para interactuar con la implementación nativa en PHP:

- `/api/facturacion-electronica/php/generar-xml`
- `/api/facturacion-electronica/php/generar-cdc`
- `/api/facturacion-electronica/php/validar-datos`
- `/api/facturacion-electronica/php/generar-xml-evento-cancelacion`
- `/api/facturacion-electronica/php/generar-xml-evento-inutilizacion`

## Uso de la Nueva Implementación

### Desde Controladores o Modelos

```php
<?php

namespace App\Http\Controllers;

use App\Services\FacturacionElectronica\FacturacionElectronicaServiceV2;
use Illuminate\Http\Request;

class MiControlador extends Controller
{
    protected $facturacionService;
    
    public function __construct(FacturacionElectronicaServiceV2 $facturacionService)
    {
        $this->facturacionService = $facturacionService;
    }
    
    public function generarFactura(Request $request)
    {
        $params = config('facturacion_electronica.contribuyente');
        $params['actividadesEconomicas'] = config('facturacion_electronica.actividades_economicas');
        $params['establecimientos'] = config('facturacion_electronica.establecimientos');
        
        $data = $request->all(); // Datos de la factura
        
        $xml = $this->facturacionService->generateXML($params, $data);
        
        // Hacer algo con el XML...
        
        return response($xml, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="factura.xml"');
    }
}
```

### Desde la API

La API mantiene la misma estructura de solicitud que la implementación anterior, pero utilizando las nuevas rutas:

```
POST /api/facturacion-electronica/php/generar-xml
```

Cuerpo de la solicitud:

```json
{
  "params": {
    "version": 150,
    "ruc": "80069563-1",
    "razonSocial": "DE generado en ambiente de prueba - sin valor comercial ni fiscal",
    "nombreFantasia": "EMPRESA S.A.",
    "actividadesEconomicas": [{
      "codigo": "1254",
      "descripcion": "Desarrollo de Software"
    }],
    "timbradoNumero": "12558946",
    "timbradoFecha": "2022-08-25",
    "tipoContribuyente": 2,
    "tipoRegimen": 8,
    "establecimientos": [{
      "codigo": "001",
      "direccion": "Barrio Carolina",
      "numeroCasa": "0",
      "complementoDireccion1": "Entre calle 2",
      "complementoDireccion2": "y Calle 7",
      "departamento": 11,
      "departamentoDescripcion": "ALTO PARANA",
      "distrito": 145,
      "distritoDescripcion": "CIUDAD DEL ESTE",
      "ciudad": 3432,
      "ciudadDescripcion": "PUERTO PTE.STROESSNER (MUNIC)",
      "telefono": "0973-527155",
      "email": "empresa@empresa.com.py",
      "denominacion": "Sucursal 1"
    }]
  },
  "data": {
    "tipoDocumento": 1,
    "establecimiento": "001",
    "codigoSeguridadAleatorio": "298398",
    "punto": "001",
    "numero": "0000001",
    "descripcion": "Aparece en el documento",
    "observacion": "Cualquier informacion de marketing, publicidad, sorteos, promociones para el Receptor",
    "fecha": "2022-08-14T10:11:00",
    "tipoEmision": 1,
    "tipoTransaccion": 1,
    "tipoImpuesto": 1,
    "moneda": "PYG",
    "condicionAnticipo": 1,
    "condicionTipoCambio": 1,
    "descuentoGlobal": 0,
    "anticipoGlobal": 0,
    "cambio": 6700,
    "cliente": {
      "contribuyente": true,
      "ruc": "2005001-1",
      "razonSocial": "Cliente Ejemplo",
      "nombreFantasia": "Cliente Ejemplo",
      "tipoOperacion": 1,
      "direccion": "Avda Calle Segunda y Proyectada",
      "numeroCasa": "1515",
      "departamento": 11,
      "departamentoDescripcion": "ALTO PARANA",
      "distrito": 143,
      "distritoDescripcion": "DOMINGO MARTINEZ DE IRALA",
      "ciudad": 3344,
      "ciudadDescripcion": "PASO ITA (INDIGENA)",
      "pais": "PRY",
      "paisDescripcion": "Paraguay",
      "tipoContribuyente": 1,
      "documentoTipo": 1,
      "documentoNumero": "2324234",
      "telefono": "061-575903",
      "celular": "0973-809103",
      "email": "cliente@empresa.com",
      "codigo": "1548"
    }
  }
}
```

## Migración Gradual

Para facilitar la transición, se mantienen las rutas originales que utilizan el servicio Node.js, mientras se agregan nuevas rutas con el prefijo `/php` para la implementación nativa. Esto permite una migración gradual de los sistemas existentes.

### Plan de Migración Recomendado

1. Implementar la nueva versión en paralelo con la existente
2. Probar exhaustivamente la nueva implementación
3. Migrar gradualmente los sistemas que utilizan la API
4. Una vez que todos los sistemas estén migrados, eliminar la dependencia de Node.js

## Diferencias con la Implementación Original

- **Estructura de datos**: Se mantiene la misma estructura de datos de entrada y salida para facilitar la migración.
- **Validación**: La implementación nativa incluye validaciones más estrictas según el manual técnico de SIFEN.
- **Rendimiento**: La implementación nativa elimina la sobrecarga de comunicación HTTP entre servicios.

## Pendientes

1. Implementar más eventos según el manual técnico de SIFEN
2. Mejorar la validación de datos según actualizaciones del manual técnico
3. Implementar pruebas automatizadas para la nueva implementación

## Conclusión

La migración a una implementación nativa en PHP simplifica la arquitectura, mejora la integración con Laravel y reduce las dependencias externas. La estructura modular permite una fácil extensión y mantenimiento futuro.