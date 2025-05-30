# Implementación de Facturación Electrónica Paraguay en Laravel

Este proyecto implementa una integración completa entre Laravel 10 y el módulo NodeJS [facturacionelectronicapy-xmlgen](https://github.com/TIPS-SA/facturacionelectronicapy-xmlgen) para la gestión y generación de documentos electrónicos para SIFEN (Sistema Integrado de Facturación Electrónica Nacional) de Paraguay.

## Estructura de la Implementación

La implementación consta de dos partes principales:

1. **Aplicación Laravel**: Un sistema completo para gestionar facturas electrónicas, incluyendo:
   - Modelos para facturas electrónicas y sus eventos
   - Controladores para la gestión de facturas
   - Vistas para la interfaz de usuario
   - Servicios para comunicarse con el servicio Node.js
   - Generación de códigos QR según especificaciones de SIFEN

2. **Servicio Node.js**: Un servidor Express que utiliza la librería `facturacionelectronicapy-xmlgen` para:
   - Generar documentos XML para SIFEN
   - Generar XML para eventos (cancelación, inutilización, etc.)
   - Firmar digitalmente los documentos XML
   - Validación de datos según el manual técnico de SIFEN

## Requisitos

- PHP 8.1 o superior
- Laravel 10
- Node.js 14 o superior
- NPM o Yarn

## Instalación

### 1. Configuración del Servicio Node.js

```bash
# Navegar al directorio del servicio Node.js
cd node-service

# Instalar dependencias
npm install

# Iniciar el servicio
npm start
```

El servicio Node.js estará disponible en `http://localhost:3000`.

### 2. Configuración de Laravel

Agregar las siguientes variables de entorno en el archivo `.env`:

```
FACTURACION_ELECTRONICA_NODE_API_URL=http://localhost:3000
FACTURACION_ELECTRONICA_RUC=80069563-1
FACTURACION_ELECTRONICA_RAZON_SOCIAL="Empresa de Ejemplo S.A."
FACTURACION_ELECTRONICA_NOMBRE_FANTASIA="Empresa de Ejemplo"
FACTURACION_ELECTRONICA_TIMBRADO_NUMERO=12558946
FACTURACION_ELECTRONICA_TIMBRADO_FECHA=2023-01-01
FACTURACION_ELECTRONICA_TIPO_CONTRIBUYENTE=2
FACTURACION_ELECTRONICA_TIPO_REGIMEN=8
```

Publicar el archivo de configuración:

```bash
php artisan vendor:publish --tag=facturacion-electronica-config
```

## Uso

### Endpoints de la API

La implementación expone los siguientes endpoints:

#### 1. Generar XML

```
POST /api/facturacion-electronica/generar-xml
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

#### 2. Generar CDC

```
POST /api/facturacion-electronica/generar-cdc
```

Cuerpo de la solicitud:

```json
{
  "data": {
    "tipoDocumento": 1,
    "establecimiento": "001",
    "punto": "001",
    "numero": "0000001"
  }
}
```

#### 3. Validar Datos

```
POST /api/facturacion-electronica/validar-datos
```

Cuerpo de la solicitud:

```json
{
  "data": {
    "tipoDocumento": 1,
    "establecimiento": "001",
    "punto": "001",
    "numero": "0000001",
    "fecha": "2022-08-14T10:11:00"
  }
}
```

## Uso desde PHP

Puedes utilizar el servicio directamente desde tus controladores o modelos:

```php
<?php

namespace App\Http\Controllers;

use App\Services\FacturacionElectronica\FacturacionElectronicaService;
use Illuminate\Http\Request;

class MiControlador extends Controller
{
    protected $facturacionService;
    
    public function __construct(FacturacionElectronicaService $facturacionService)
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

## Funcionalidades Implementadas

- Generación de XML para documentos electrónicos según especificaciones de SIFEN
- Gestión completa de facturas electrónicas (CRUD)
- Eventos para cancelación, inutilización, conformidad, disconformidad
- Generación de códigos QR según el formato requerido por SIFEN
- Firma digital de documentos XML (implementación básica)
- Validación de datos según reglas del manual técnico

## Uso de la Aplicación

### Generación de Facturas

1. Acceda a la página principal de facturas `/facturas`
2. Haga clic en "Nueva Factura" para crear una nueva factura
3. Complete el formulario con los datos requeridos:
   - Tipo de documento
   - Datos del receptor
   - Detalle de ítems
4. Al guardar, se generará automáticamente:
   - El XML según el formato requerido por SIFEN
   - El código QR con los datos de la factura
   - El código CDC para identificación del documento

### Consulta y Descarga

1. En el listado de facturas, puede ver todas las facturas generadas
2. Haga clic en "Ver detalle" para ver la información completa de una factura
3. Desde la vista de detalle puede:
   - Descargar el XML generado
   - Ver el código QR
   - Consultar el historial de eventos

### Cancelación de Facturas

1. En la vista de detalle de una factura aceptada, puede cancelarla
2. Complete el formulario de cancelación indicando el motivo
3. El sistema generará el XML del evento de cancelación y actualizará el estado

## Implementación Pendiente

1. Integración completa con el API de SIFEN para:
   - Envío de documentos
   - Consulta de estado
   - Recepción de respuestas
2. Completar firma digital con certificados reales
3. Implementación de tabla detalle de facturas
4. Manejo de errores avanzado
5. Pruebas automatizadas

## Notas Importantes

- Esta implementación es un puente entre Laravel y la librería Node.js `facturacionelectronicapy-xmlgen`.
- El servicio Node.js debe estar en ejecución para que la integración funcione correctamente.
- Para un entorno de producción, considere implementar medidas de seguridad adicionales y asegurarse de que el servicio Node.js esté protegido adecuadamente.

## Recursos Adicionales

- [Documentación oficial de facturacionelectronicapy-xmlgen](https://github.com/TIPS-SA/facturacionelectronicapy-xmlgen)
- [Manual Técnico de SIFEN](https://www.set.gov.py/portal/PARAGUAY-SET/detail?folder-id=repository:collaboration:/sites/PARAGUAY-SET/categories/SET/Factura%20Electr%C3%B3nica/documentos-tecnicos&content-id=/repository/collaboration/sites/PARAGUAY-SET/documents/FE/documentos-tecnicos/Manual%20T%C3%A9cnico%20Versi%C3%B3n%20150.pdf)