# Gestión de Certificados Digitales - SIFEN Paraguay

## Descripción

Este sistema centraliza la gestión de certificados digitales para la autenticación con SIFEN (Sistema Integrado de Facturación Electrónica) de Paraguay. El servicio está diseñado para eliminar código duplicado y proporcionar una gestión consistente de certificados en toda la aplicación.

## Estructura del Sistema

### Servicio de Certificados (`services/certificados.service.js`)

El servicio centralizado incluye las siguientes funciones:

#### `cargarCertificado(clavePersonalizada)`
- **Descripción**: Busca y carga certificados desde ubicaciones predefinidas
- **Parámetros**: 
  - `clavePersonalizada`: Clave opcional para el certificado
- **Retorna**: Objeto con información del certificado cargado
- **Ubicaciones buscadas**:
  1. `./certificado.p12`
  2. `./cert/certificado.p12`
  3. `../storage/app/certificados/certificado.p12`

#### `validarCertificado(certificadoBuffer, clave)`
- **Descripción**: Valida que un certificado sea válido y accesible
- **Parámetros**:
  - `certificadoBuffer`: Buffer del certificado a validar
  - `clave`: Clave del certificado
- **Retorna**: Objeto con resultado de validación

#### `prepararOpcionesConCertificado(options, clavePersonalizada)`
- **Descripción**: Prepara las opciones agregando certificados si no están presentes
- **Parámetros**:
  - `options`: Opciones existentes
  - `clavePersonalizada`: Clave opcional personalizada
- **Retorna**: Opciones actualizadas con certificados

#### `obtenerEstadoCertificados()`
- **Descripción**: Obtiene información detallada sobre certificados disponibles
- **Retorna**: Estado completo de certificados en el sistema

## Configuración

### Variables de Entorno

- `CERT_PASSWORD`: Contraseña del certificado digital (recomendado)

### Ubicaciones de Certificados

El sistema busca certificados en las siguientes ubicaciones (en orden de prioridad):

1. **Directorio raíz del servicio**: `./certificado.p12`
2. **Subdirectorio cert**: `./cert/certificado.p12`
3. **Storage de Laravel**: `../storage/app/certificados/certificado.p12`

## Uso en Endpoints

### Función Helper

El sistema incluye una función helper `cargarCertificadoHelper()` que se usa en todos los endpoints que requieren certificados:

```javascript
// Cargar certificado usando función helper
await cargarCertificadoHelper(options, 'contexto de operación');
```

### Endpoints que Usan Certificados

1. **POST `/consultar-estado-documento`**: Consulta estado de documentos en SIFEN
2. **POST `/enviar-documento`**: Envía documentos a SIFEN
3. **GET `/verificar-conexion-sifen`**: Verifica conectividad con SIFEN
4. **GET `/estado-certificados`**: Obtiene información sobre certificados disponibles

## Beneficios del Sistema Centralizado

### Eliminación de Código Duplicado
- **Antes**: Cada endpoint tenía su propia lógica de carga de certificados
- **Después**: Servicio centralizado reutilizable

### Mejor Manejo de Errores
- Validación consistente de certificados
- Logs estructurados y descriptivos
- Manejo robusto de errores

### Facilidad de Mantenimiento
- Cambios en una sola ubicación
- Configuración centralizada
- Fácil diagnóstico de problemas

### Funcionalidades Mejoradas
- Validación de formato de certificados
- Información detallada sobre estado
- Múltiples ubicaciones de búsqueda

## Diagnóstico y Troubleshooting

### Verificar Estado de Certificados

```bash
# Obtener información sobre certificados disponibles
curl -X GET http://localhost:3000/estado-certificados
```

### Verificar Conexión con SIFEN

```bash
# Verificar conectividad y autenticación
curl -X GET http://localhost:3000/verificar-conexion-sifen
```

### Logs Importantes

El sistema genera logs descriptivos para cada operación:

- `Cargando certificado para [contexto] usando el servicio centralizado...`
- `Certificado cargado correctamente para [contexto]`
- `Certificado validado correctamente: [mensaje]`
- `No se encontró certificado digital para [contexto]`

### Errores Comunes

1. **Certificado no encontrado**
   - Verificar ubicaciones de búsqueda
   - Asegurar permisos de lectura

2. **Certificado inválido**
   - Verificar formato PKCS#12
   - Verificar integridad del archivo

3. **Contraseña incorrecta**
   - Verificar variable `CERT_PASSWORD`
   - Verificar contraseña del certificado

## Migración desde Código Anterior

### Cambios Realizados

1. **Eliminado**: Código duplicado de carga de certificados en endpoints
2. **Agregado**: Servicio centralizado de certificados
3. **Mejorado**: Validación y manejo de errores
4. **Agregado**: Endpoint de diagnóstico

### Compatibilidad

El sistema mantiene compatibilidad completa con:
- Parámetros existentes de endpoints
- Estructura de respuestas
- Variables de entorno
- Ubicaciones de certificados

## Próximos Pasos

- [ ] Implementar cache de certificados para mejorar rendimiento
- [ ] Agregar soporte para múltiples certificados
- [ ] Implementar rotación automática de certificados
- [ ] Agregar métricas y monitoreo de uso de certificados
