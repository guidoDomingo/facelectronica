# Implementación de Facturación Electrónica Paraguay en Laravel 10

Este proyecto implementa una integración entre Laravel 10 y el módulo NodeJS [facturacionelectronicapy-xmlgen](https://github.com/TIPS-SA/facturacionelectronicapy-xmlgen) para la generación de documentos electrónicos para SIFEN (Paraguay).

## Requisitos

- PHP 8.1 o superior
- Laravel 10
- Node.js 14 o superior
- NPM o Yarn

## Instalación

### 1. Configuración del Servicio Node.js

Primero, necesitamos configurar el servicio Node.js que utilizará la librería `facturacionelectronicapy-xmlgen`:

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

## Estructura de la Implementación

La implementación consta de dos partes principales:

1. **Servicio Laravel**: Un servicio que actúa como cliente para comunicarse con el servicio Node.js.
   - `app/Services/FacturacionElectronica/FacturacionElectronicaService.php`: Servicio principal que se comunica con el API Node.js.
   - `app/Http/Controllers/FacturacionElectronicaController.php`: Controlador que expone endpoints API para generar XML, CDC y validar datos.
   - `app/Http/Controllers/EjemploFacturacionController.php`: Controlador de ejemplo para mostrar el uso del servicio.
   - `config/facturacion_electronica.php`: Configuración del servicio.

2. **Servicio Node.js**: Un servidor Express que utiliza la librería `facturacionelectronicapy-xmlgen` para generar los documentos XML.
   - `node-service/index.js`: Servidor Express que implementa los endpoints necesarios.
   - `node-service/package.json`: Dependencias del servicio Node.js.

## Uso

### Ejemplo Web

Puedes acceder a una interfaz web de ejemplo en:

```
http://localhost/facelec/public/facturacion
```

Esta interfaz te permitirá:
- Generar un XML de ejemplo para SIFEN
- Generar un CDC (Código de Control) de ejemplo

### API REST

La implementación expone los siguientes endpoints API:

#### 1. Generar XML

```
POST /api/facturacion-electronica/generar-xml
```

#### 2. Generar CDC

```
POST /api/facturacion-electronica/generar-cdc
```

#### 3. Validar Datos

```
POST /api/facturacion-electronica/validar-datos
```

Consulta el archivo `README-FACTURACION.md` para ver ejemplos detallados de cómo utilizar estos endpoints.

## Documentación Adicional

Para más detalles sobre la implementación y ejemplos de uso, consulta:

- `README-FACTURACION.md`: Documentación detallada sobre la implementación y ejemplos de uso.

## Notas Importantes

- Esta implementación es un puente entre Laravel y la librería Node.js `facturacionelectronicapy-xmlgen`.
- El servicio Node.js debe estar en ejecución para que la integración funcione correctamente.
- Para un entorno de producción, considere implementar medidas de seguridad adicionales y asegurarse de que el servicio Node.js esté protegido adecuadamente.