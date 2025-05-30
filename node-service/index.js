/**
 * Servicio Node.js para Facturación Electrónica Paraguay
 * 
 * Este servicio implementa una API REST que utiliza el módulo facturacionelectronicapy-xmlgen
 * para generar documentos XML para SIFEN (Paraguay).
 */

const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const facturacionService = require('./services/facturacion-electronica.service');
const sifenService = require('./services/sifen.service');
const eventosService = require('./services/eventos.service');
const signatureService = require('./services/signature.service');
const certificadosService = require('./services/certificados.service');

const app = express();
const port = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json({ limit: '10mb' }));
app.use(bodyParser.urlencoded({ extended: true }));

// Ruta principal
app.get('/', (req, res) => {
    res.json({
        message: 'API de Facturación Electrónica Paraguay',
        version: '1.1.0',
        endpoints: [
            { method: 'POST', path: '/generate-xml', description: 'Genera un XML para SIFEN' },
            { method: 'POST', path: '/generate-cdc', description: 'Genera un CDC (Código de Control)' },
            { method: 'POST', path: '/validate-data', description: 'Valida datos según el manual técnico de SIFEN' },
            { method: 'POST', path: '/generate-xml-evento-cancelacion', description: 'Genera un XML para evento de cancelación' },
            { method: 'POST', path: '/generate-xml-evento-inutilizacion', description: 'Genera un XML para evento de inutilización' },
            { method: 'POST', path: '/generate-xml-evento-conformidad', description: 'Genera un XML para evento de conformidad' },
            { method: 'POST', path: '/generate-xml-evento-disconformidad', description: 'Genera un XML para evento de disconformidad' },
            { method: 'POST', path: '/generate-xml-evento-desconocimiento', description: 'Genera un XML para evento de desconocimiento' },
            { method: 'POST', path: '/generate-xml-evento-notificacion', description: 'Genera un XML para evento de notificación' },
            { method: 'GET', path: '/get-ciudad/:ciudadId', description: 'Obtiene información de una ciudad por su ID' },
            { method: 'POST', path: '/consultar-estado-documento', description: 'Consulta el estado de un documento en SIFEN por su CDC' },
            { method: 'POST', path: '/enviar-documento', description: 'Envía un documento XML a SIFEN' },            { method: 'POST', path: '/enviar-evento-inutilizacion', description: 'Envía un evento de inutilización a SIFEN' },
            { method: 'POST', path: '/enviar-evento-notificacion', description: 'Envía un evento de notificación a SIFEN' },
            { method: 'POST', path: '/sign-xml', description: 'Firma un XML con certificado digital' },
            { method: 'GET', path: '/estado-certificados', description: 'Obtiene el estado de los certificados disponibles' },
            { method: 'GET', path: '/verificar-conexion-sifen', description: 'Verifica la conexión con SIFEN' }
        ]
    });
});

// Endpoint para generar XML
app.post('/generate-xml', async (req, res) => {
    try {
        const { params, data, options } = req.body;
        
        if (!params || !data) {
            return res.status(400).json({
                success: false,
                message: 'Se requieren los parámetros "params" y "data"'
            });
        }
        
        const xml = await facturacionService.generateXML(params, data, options || {});
        
        res.set('Content-Type', 'application/xml');
        res.send(xml);
    } catch (error) {
        console.error('Error al generar XML:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al generar XML'
        });
    }
});

// Endpoint para generar CDC
app.post('/generate-cdc', async (req, res) => {
    try {
        const { data } = req.body;
        
        if (!data) {
            return res.status(400).json({
                success: false,
                message: 'Se requiere el parámetro "data"'
            });
        }
          // Generar CDC utilizando la librería
        // Nota: La librería no expone directamente esta funcionalidad, por lo que
        // se implementa aquí siguiendo las especificaciones técnicas de SIFEN.
        const cdc = await generateCDC(data);
        
        res.json({
            success: true,
            cdc: cdc
        });
    } catch (error) {
        console.error('Error al generar CDC:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al generar CDC'
        });
    }
});

// Endpoint para validar datos
app.post('/validate-data', async (req, res) => {
    try {
        const { data } = req.body;
        
        if (!data) {
            return res.status(400).json({
                success: false,
                message: 'Se requiere el parámetro "data"'
            });
        }
        
        // Validar datos utilizando la librería
        // Nota: La librería no expone directamente esta funcionalidad, por lo que
        // se simula aquí. En una implementación real, se debería usar la función
        // correspondiente de la librería.
        const validationResult = await simulateValidateData(data);
        
        if (validationResult.valid) {
            res.json({
                success: true,
                valid: true
            });
        } else {
            res.json({
                success: true,
                valid: false,
                errors: validationResult.errors
            });
        }
    } catch (error) {
        console.error('Error al validar datos:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al validar datos'
        });
    }
});

// Endpoint para generar XML de evento de cancelación
app.post('/generate-xml-evento-cancelacion', async (req, res) => {
    try {
        const { id, params, data, options } = req.body;
        
        if (!id || !params || !data) {
            return res.status(400).json({
                success: false,
                message: 'Se requieren los parámetros "id", "params" y "data"'
            });
        }
        
        const xml = await facturacionService.generateXMLEventoCancelacion(id, params, data, options || {});
        
        res.set('Content-Type', 'application/xml');
        res.send(xml);
    } catch (error) {
        console.error('Error al generar XML de evento de cancelación:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al generar XML de evento de cancelación'
        });
    }
});

// Endpoint para generar XML de evento de inutilización
app.post('/generate-xml-evento-inutilizacion', async (req, res) => {
    try {
        const { id, params, data, options } = req.body;
        
        if (!id || !params || !data) {
            return res.status(400).json({
                success: false,
                message: 'Se requieren los parámetros "id", "params" y "data"'
            });
        }
        
        const xml = await facturacionService.generateXMLEventoInutilizacion(id, params, data, options || {});
        
        res.set('Content-Type', 'application/xml');
        res.send(xml);
    } catch (error) {
        console.error('Error al generar XML de evento de inutilización:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al generar XML de evento de inutilización'
        });
    }
});

// Endpoint para generar XML de evento de conformidad
app.post('/generate-xml-evento-conformidad', async (req, res) => {
    try {
        const { id, params, data, options } = req.body;
        
        if (!id || !params || !data) {
            return res.status(400).json({
                success: false,
                message: 'Se requieren los parámetros "id", "params" y "data"'
            });
        }
        
        const xml = await facturacionService.generateXMLEventoConformidad(id, params, data, options || {});
        
        res.set('Content-Type', 'application/xml');
        res.send(xml);
    } catch (error) {
        console.error('Error al generar XML de evento de conformidad:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al generar XML de evento de conformidad'
        });
    }
});

// Endpoint para generar XML de evento de disconformidad
app.post('/generate-xml-evento-disconformidad', async (req, res) => {
    try {
        const { id, params, data, options } = req.body;
        
        if (!id || !params || !data) {
            return res.status(400).json({
                success: false,
                message: 'Se requieren los parámetros "id", "params" y "data"'
            });
        }
        
        const xml = await facturacionService.generateXMLEventoDisconformidad(id, params, data, options || {});
        
        res.set('Content-Type', 'application/xml');
        res.send(xml);
    } catch (error) {
        console.error('Error al generar XML de evento de disconformidad:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al generar XML de evento de disconformidad'
        });
    }
});

// Endpoint para generar XML de evento de desconocimiento
app.post('/generate-xml-evento-desconocimiento', async (req, res) => {
    try {
        const { id, params, data, options } = req.body;
        
        if (!id || !params || !data) {
            return res.status(400).json({
                success: false,
                message: 'Se requieren los parámetros "id", "params" y "data"'
            });
        }
        
        const xml = await facturacionService.generateXMLEventoDesconocimiento(id, params, data, options || {});
        
        res.set('Content-Type', 'application/xml');
        res.send(xml);
    } catch (error) {
        console.error('Error al generar XML de evento de desconocimiento:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al generar XML de evento de desconocimiento'
        });
    }
});

// Endpoint para generar XML de evento de notificación
app.post('/generate-xml-evento-notificacion', async (req, res) => {
    try {
        const { id, params, data, options } = req.body;
        
        if (!id || !params || !data) {
            return res.status(400).json({
                success: false,
                message: 'Se requieren los parámetros "id", "params" y "data"'
            });
        }
        
        const xml = await facturacionService.generateXMLEventoNotificacion(id, params, data, options || {});
        
        res.set('Content-Type', 'application/xml');
        res.send(xml);
    } catch (error) {
        console.error('Error al generar XML de evento de notificación:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al generar XML de evento de notificación'
        });
    }
});

// Endpoint para obtener información de una ciudad
app.get('/get-ciudad/:ciudadId', async (req, res) => {
    try {
        const { ciudadId } = req.params;
        
        if (!ciudadId) {
            return res.status(400).json({
                success: false,
                message: 'Se requiere el parámetro "ciudadId"'
            });
        }
        
        const ciudad = facturacionService.getCiudad(parseInt(ciudadId));
        
        if (ciudad) {
            res.json({
                success: true,
                ciudad: ciudad
            });
        } else {
            res.status(404).json({
                success: false,
                message: 'Ciudad no encontrada'
            });
        }
    } catch (error) {
        console.error('Error al obtener información de ciudad:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al obtener información de ciudad'
        });
    }
});

// Endpoint para firmar XML
app.post('/sign-xml', async (req, res) => {
    try {
        const { xml, certPath, certPassword, options } = req.body;
        
        if (!xml || !certPath || !certPassword) {
            return res.status(400).json({
                success: false,
                message: 'Se requieren los parámetros "xml", "certPath" y "certPassword"'
            });
        }
        
        // Verificar que exista el certificado
        const fs = require('fs');
        if (!fs.existsSync(certPath)) {
            return res.status(400).json({
                success: false,
                message: `No se encontró el certificado en la ruta: ${certPath}`
            });
        }
        
        console.log(`Procesando solicitud de firma XML: ${xml.substring(0, 100)}...`);
        
        // Usar el servicio de firma
        const signedXml = await signatureService.signXML(xml, certPath, certPassword, options || {});
        
        // Verificar la firma (opcional)
        if (options && options.verify === true) {
            const verificationResult = await signatureService.verifyXMLSignature(signedXml);
            if (!verificationResult.valid) {
                console.error('Verificación de firma fallida:', verificationResult.error);
                return res.status(500).json({
                    success: false,
                    message: `XML firmado pero la verificación falló: ${verificationResult.error}`
                });
            }
        }
        
        res.set('Content-Type', 'application/xml');
        res.send(signedXml);
    } catch (error) {
        console.error('Error al firmar XML:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al firmar XML'
        });
    }
});

// Función para generar CDC según especificaciones de SIFEN
// CDC Format: Type(2) + RUC(8) + DV(1) + Establishment(3) + Point(3) + Number(7) + Date(8) + Random(8) + Security(4) = 44 digits
async function generateCDC(data) {
    // Validar y normalizar datos de entrada
    const tipoDocumento = String(data.tipoDocumento || 1).padStart(2, '0');
    const establecimiento = String(data.establecimiento || '001').padStart(3, '0');
    const punto = String(data.punto || '001').padStart(3, '0');
    const numero = String(data.numero || 1).padStart(7, '0');
    
    // RUC del emisor (desde configuración o datos)
    const rucCompleto = data.ruc || '800695631'; // RUC completo con DV
    const ruc = rucCompleto.replace(/[^0-9]/g, '').substring(0, 8); // Solo números, primeros 8 dígitos
    const dv = rucCompleto.replace(/[^0-9]/g, '').substring(8, 9) || '1'; // Dígito verificador
    
    // Fecha actual en formato YYYYMMDD
    const now = new Date();
    const fecha = now.getFullYear().toString() + 
                  (now.getMonth() + 1).toString().padStart(2, '0') + 
                  now.getDate().toString().padStart(2, '0');
    
    // Número aleatorio de 8 dígitos
    const random = Math.floor(Math.random() * 99999999).toString().padStart(8, '0');
    
    // Código de seguridad aleatorio de 4 dígitos
    const codigoSeguridad = Math.floor(Math.random() * 9999).toString().padStart(4, '0');
    
    // Construir CDC final
    const cdc = tipoDocumento + ruc + dv + establecimiento + punto + numero + fecha + random + codigoSeguridad;
    
    // Verificar que tenga exactamente 44 dígitos
    if (cdc.length !== 44) {
        throw new Error(`CDC debe tener 44 dígitos, generado: ${cdc.length} dígitos. CDC: ${cdc}`);
    }
    
    // Verificar que solo contenga números
    if (!/^\d{44}$/.test(cdc)) {
        throw new Error(`CDC debe contener solo números. CDC generado: ${cdc}`);
    }
    
    console.log(`CDC generado correctamente: ${cdc} (${cdc.length} dígitos)`);
    return cdc;
}

// Función auxiliar para simular la validación de datos
// En una implementación real, esto debería usar la funcionalidad de la librería
async function simulateValidateData(data) {
    // Simulación simple de validación
    const errors = [];
    
    // Validar campos requeridos básicos
    if (!data.tipoDocumento) errors.push('El campo tipoDocumento es requerido');
    if (!data.establecimiento) errors.push('El campo establecimiento es requerido');
    if (!data.punto) errors.push('El campo punto es requerido');
    if (!data.numero) errors.push('El campo numero es requerido');
    if (!data.fecha) errors.push('El campo fecha es requerido');
    
    return {
        valid: errors.length === 0,
        errors: errors
    };
}

// Endpoint para consultar el estado de un documento en SIFEN
app.post('/consultar-estado-documento', async (req, res) => {
    try {
        const { cdc, ambiente, options = {} } = req.body;
        if (!cdc) {
            return res.status(400).json({
                success: false,
                message: 'Se requiere el parámetro "cdc"'
            });
        }
        
        // Validar formato de CDC
        const cdcRegex = /^[0-9]{44}$/;
        if (!cdcRegex.test(cdc)) {
            return res.status(400).json({
                success: false,
                message: 'El CDC debe tener 44 dígitos numéricos'
            });
        }
        
        // Forzar uso de API real de SIFEN sin importar el entorno
        options.simular = false;
        console.log('Forzando conexión real a SIFEN-Test, no se permite simulación');
        
        // Ambiente por defecto: 'test'
        const ambienteActual = ambiente || 'test';        console.log(`Consultando estado del documento ${cdc} en ambiente ${ambienteActual}`);
        
        // Cargar certificado usando función helper
        await cargarCertificadoHelper(options, 'consulta de estado de documento');
        
        console.log('Opciones preparadas:', JSON.stringify({
            ...options,
            certificado: options.certificado ? 'CERTIFICADO_CARGADO' : 'NO_DISPONIBLE',
            clave: options.clave ? 'CLAVE_CONFIGURADA' : 'NO_DISPONIBLE'
        }));
        
        // Verificar que la función existe
        if (typeof sifenService.consultarEstadoDocumento !== 'function') {
            console.error('La función consultarEstadoDocumento no está disponible en sifenService');
            return res.status(500).json({
                success: false,
                message: 'Error interno: Función de consulta no implementada'
            });
        }
        
        // Llamar al servicio de consulta
        const resultado = await sifenService.consultarEstadoDocumento(cdc, ambienteActual, options);
        
        // Verificar si hubo algún error en la consulta
        if (resultado.estado === 'error') {
            console.warn('Error en la consulta del documento:', resultado.respuesta.mensaje);
            return res.status(400).json({
                success: false,
                resultado: resultado,
                message: resultado.respuesta.mensaje
            });
        }
        
        // Agregar información sobre la registración del documento
        let documentoRegistrado = false;
        
        // Verificar si el documento está registrado en SIFEN según el código de respuesta
        if (resultado?.respuesta?.codigo === '0' || 
            resultado?.respuesta?.codigo === '0001' ||
            resultado?.respuesta?.respuestaCompleta?.dEstado === 'RECIBIDO') {
            documentoRegistrado = true;
        }
        
        res.json({
            success: true,
            documentoRegistrado: documentoRegistrado,
            resultado: resultado
        });
    } catch (error) {
        console.error('Error al consultar estado del documento:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al consultar estado del documento'
        });
    }
});

// Endpoint para enviar un documento a SIFEN
app.post('/enviar-documento', async (req, res) => {
    try {
        const { xml, ambiente, options = {} } = req.body;
        
        if (!xml) {
            return res.status(400).json({
                success: false,
                message: 'Se requiere el parámetro "xml"'
            });
        }
        
        // Validación básica del XML
        if (!xml.includes('<?xml') || !xml.includes('<DE>')) {
            return res.status(400).json({
                success: false,
                message: 'El XML proporcionado no parece ser un documento electrónico válido'
            });
        }
        
        // Forzar uso de API real de SIFEN sin importar el entorno
        options.simular = false;
        console.log('Forzando conexión real a SIFEN-Test, no se permite simulación');
        
        // Ambiente por defecto: 'test'
        const ambienteActual = ambiente || 'test';        console.log(`Enviando documento a SIFEN en ambiente ${ambienteActual}, tamaño XML: ${xml.length} bytes`);
        
        // Cargar certificado usando función helper
        await cargarCertificadoHelper(options, 'envío de documento');
        
        console.log('Opciones preparadas:', JSON.stringify({
            ...options,
            certificado: options.certificado ? 'CERTIFICADO_CARGADO' : 'NO_DISPONIBLE',
            clave: options.clave ? 'CLAVE_CONFIGURADA' : 'NO_DISPONIBLE'
        }));
        
        // Verificar que la función existe
        if (typeof sifenService.enviarDocumento !== 'function') {
            console.error('La función enviarDocumento no está disponible en sifenService');
            return res.status(500).json({
                success: false,
                message: 'Error interno: Función de envío no implementada'
            });
        }
        
        // Llamar al servicio de envío
        const resultado = await sifenService.enviarDocumento(xml, ambienteActual, options);
        
        // Verificar si hubo algún error en el envío
        if (resultado.estado === 'error') {
            console.warn('Error en el envío del documento:', resultado.recepcion.mensaje);
            return res.status(400).json({
                success: false,
                resultado: resultado,
                message: resultado.recepcion.mensaje
            });
        }
        
        res.json({
            success: true,
            resultado: resultado
        });
    } catch (error) {
        console.error('Error al enviar documento a SIFEN:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al enviar documento a SIFEN'
        });
    }
});

// Iniciar el servidor
app.listen(port, () => {
    console.log(`Servidor de Facturación Electrónica ejecutándose en http://localhost:${port}`);
});

// Endpoint para enviar un evento de inutilización a SIFEN
app.post('/enviar-evento-inutilizacion', async (req, res) => {
    try {
        const { xml, ambiente, options = {} } = req.body;
        
        if (!xml) {
            return res.status(400).json({
                success: false,
                message: 'Se requiere el parámetro "xml"'
            });
        }
        
        // Verificar si estamos en modo desarrollo para simulación
        const isDev = process.env.NODE_ENV !== 'production';
        
        // Si estamos en desarrollo y no se especificó lo contrario, simulamos
        if (isDev && options.simular !== false) {
            options.simular = true;
        }
        
        // Ambiente por defecto: 'test'
        const ambienteActual = ambiente || 'test';
        
        console.log(`Enviando evento de inutilización a SIFEN en ambiente ${ambienteActual}`);
        
        // Llamar al servicio de eventos
        const resultado = await eventosService.enviarEventoInutilizacion(xml, ambienteActual, options);
        
        // Verificar si hubo algún error en el envío
        if (resultado.estado === 'error') {
            console.warn('Error en el envío del evento:', resultado.evento.mensaje);
            return res.status(400).json({
                success: false,
                resultado: resultado,
                message: resultado.evento.mensaje
            });
        }
        
        res.json({
            success: true,
            resultado: resultado
        });
    } catch (error) {
        console.error('Error al enviar evento de inutilización:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al enviar evento de inutilización'
        });
    }
});

// Endpoint para enviar un evento de notificación a SIFEN
app.post('/enviar-evento-notificacion', async (req, res) => {
    try {
        const { xml, ambiente, options = {} } = req.body;
        
        if (!xml) {
            return res.status(400).json({
                success: false,
                message: 'Se requiere el parámetro "xml"'
            });
        }
        
        // Verificar si estamos en modo desarrollo para simulación
        const isDev = process.env.NODE_ENV !== 'production';
        
        // Si estamos en desarrollo y no se especificó lo contrario, simulamos
        if (isDev && options.simular !== false) {
            options.simular = true;
        }
        
        // Ambiente por defecto: 'test'
        const ambienteActual = ambiente || 'test';
        
        console.log(`Enviando evento de notificación a SIFEN en ambiente ${ambienteActual}`);
        
        // Llamar al servicio de eventos
        const resultado = await eventosService.enviarEventoNotificacion(xml, ambienteActual, options);
        
        // Verificar si hubo algún error en el envío
        if (resultado.estado === 'error') {
            console.warn('Error en el envío del evento:', resultado.evento.mensaje);
            return res.status(400).json({
                success: false,
                resultado: resultado,
                message: resultado.evento.mensaje
            });
        }
        
        res.json({
            success: true,
            resultado: resultado
        });
    } catch (error) {
        console.error('Error al enviar evento de notificación:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al enviar evento de notificación'
        });
    }
});

// Endpoint para verificar la conexión con SIFEN
app.get('/verificar-conexion-sifen', async (req, res) => {
    try {
        console.log('Verificando conexión con SIFEN...');
        
        // Opciones para la verificación
        const options = {
            simular: false        };
        
        // Cargar certificado usando función helper
        await cargarCertificadoHelper(options, 'verificación de conexión');
        
        // Intentar realizar una consulta simple para verificar la conexión
        // Usamos un CDC de ejemplo que probablemente no exista
        const cdc = '01800695631001001000000012023052611267896453';
        
        // Realizar la consulta
        const resultado = await sifenService.consultarEstadoDocumento(cdc, 'test', options);
        
        // Preparar la respuesta con información detallada
        const respuesta = {
            success: resultado.estado !== 'error',
            estado: resultado.estado,
            conexion: resultado.estado === 'error' && resultado.respuesta.codigo === 'AUTH-001' 
                ? 'Error de autenticación (certificado inválido o no proporcionado)'
                : (resultado.estado === 'error' ? 'Error de conexión' : 'Conexión exitosa'),
            detalles: {
                certificadoDisponible: options.certificado ? true : false,
                certificadoPath: options.certificadoPath || 'No encontrado',
                respuestaSIFEN: resultado
            }
        };
        
        res.json(respuesta);
    } catch (error) {
        console.error('Error al verificar conexión con SIFEN:', error);
        res.status(500).json({
            success: false,
            estado: 'error',
            conexion: 'Error en la verificación',
            mensaje: error.message || 'Error desconocido al verificar conexión'
        });
    }
});

// Endpoint para obtener el estado de los certificados
app.get('/estado-certificados', async (req, res) => {
    try {
        console.log('Consultando estado de certificados disponibles...');
        
        // Obtener información detallada sobre certificados
        const estadoCertificados = await certificadosService.obtenerEstadoCertificados();
        
        res.json({
            success: true,
            estado: estadoCertificados
        });
    } catch (error) {
        console.error('Error al consultar estado de certificados:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al consultar estado de certificados'
        });
    }
});

// Endpoint para diagnóstico detallado de SIFEN y certificados
app.get('/diagnostico-sifen', async (req, res) => {
    try {
        console.log('🔍 Iniciando diagnóstico detallado de SIFEN...');
        
        const diagnostico = {
            timestamp: new Date().toISOString(),
            certificados: {},
            conectividad: {},
            configuracion: {},
            recomendaciones: []
        };
        
        // 1. Diagnóstico de certificados
        console.log('1️⃣ Verificando certificados disponibles...');
        const estadoCertificados = await certificadosService.obtenerEstadoCertificados();
        diagnostico.certificados = estadoCertificados;
        
        // 2. Intentar cargar certificado
        console.log('2️⃣ Intentando cargar certificado...');
        const resultadoCarga = await certificadosService.cargarCertificado();
        diagnostico.certificados.cargaExitosa = resultadoCarga.encontrado;
        diagnostico.certificados.mensajeCarga = resultadoCarga.mensaje;
        
        if (!resultadoCarga.encontrado) {
            diagnostico.recomendaciones.push('❌ No se encontró ningún certificado digital válido');
            diagnostico.recomendaciones.push('📁 Coloque un certificado .p12 en: ./certificado.p12, ./cert/certificado.p12 o ../storage/app/certificados/certificado.p12');
            diagnostico.recomendaciones.push('🔑 Configure la variable de entorno CERT_PASSWORD con la contraseña del certificado');
        }
        
        // 3. Verificar conectividad básica
        console.log('3️⃣ Verificando conectividad con SIFEN...');
        try {
            const https = require('https');
            const url = require('url');
            
            const testUrls = [
                'https://sifen-test.set.gov.py',
                'https://sifen-test.set.gov.py/de/ws/consultas-services.wsdl'
            ];
            
            diagnostico.conectividad.urls = {};
            
            for (const testUrl of testUrls) {
                try {
                    const response = await new Promise((resolve, reject) => {
                        const request = https.get(testUrl, { timeout: 10000 }, (res) => {
                            resolve({
                                statusCode: res.statusCode,
                                headers: res.headers,
                                accesible: res.statusCode < 500
                            });
                        });
                        request.on('error', reject);
                        request.on('timeout', () => reject(new Error('Timeout')));
                    });
                    
                    diagnostico.conectividad.urls[testUrl] = {
                        accesible: true,
                        statusCode: response.statusCode,
                        mensaje: `Respuesta HTTP ${response.statusCode}`
                    };
                } catch (error) {
                    diagnostico.conectividad.urls[testUrl] = {
                        accesible: false,
                        error: error.message,
                        mensaje: 'No se pudo conectar'
                    };
                }
            }
        } catch (error) {
            diagnostico.conectividad.error = error.message;
        }
        
        // 4. Configuración del sistema
        console.log('4️⃣ Verificando configuración del sistema...');
        diagnostico.configuracion = {
            nodeVersion: process.version,
            platform: process.platform,
            env: {
                NODE_ENV: process.env.NODE_ENV || 'no configurado',
                CERT_PASSWORD: process.env.CERT_PASSWORD ? 'configurado' : 'no configurado'
            },
            workingDirectory: process.cwd(),
            timestamp: new Date().toISOString()
        };
        
        // 5. Intentar consulta real si hay certificados
        if (resultadoCarga.encontrado) {
            console.log('5️⃣ Intentando consulta real a SIFEN con certificados...');
            try {
                const options = {
                    certificado: resultadoCarga.certificado,
                    clave: resultadoCarga.clave,
                    simular: false
                };
                
                // CDC de prueba que probablemente no existe
                const cdcPrueba = '01800695631001001000000012023052611267896453';
                const resultadoConsulta = await sifenService.consultarEstadoDocumento(cdcPrueba, 'test', options);
                
                diagnostico.conectividad.consultaPrueba = {
                    realizada: true,
                    resultado: resultadoConsulta,
                    exitosa: resultadoConsulta.estado !== 'error' || 
                            (resultadoConsulta.estado === 'error' && resultadoConsulta.respuesta.codigo !== 'AUTH-001')
                };
                
                if (resultadoConsulta.estado === 'error' && resultadoConsulta.respuesta.codigo === 'AUTH-001') {
                    diagnostico.recomendaciones.push('❌ Error de autenticación: El certificado no es válido o no está autorizado para SIFEN');
                    diagnostico.recomendaciones.push('🏛️ Verifique que el certificado esté registrado en SIFEN y sea válido');
                } else {
                    diagnostico.recomendaciones.push('✅ La comunicación con SIFEN funciona correctamente');
                }
                
            } catch (error) {
                diagnostico.conectividad.consultaPrueba = {
                    realizada: false,
                    error: error.message
                };
                diagnostico.recomendaciones.push(`❌ Error en consulta de prueba: ${error.message}`);
            }
        } else {
            diagnostico.recomendaciones.push('⚠️ No se puede probar la conexión sin certificados válidos');
        }
        
        // 6. Generar recomendaciones adicionales
        if (diagnostico.certificados.total === 0) {
            diagnostico.recomendaciones.push('📝 PASOS PARA SOLUCIONAR:');
            diagnostico.recomendaciones.push('   1. Obtenga un certificado digital válido (.p12) de una CA autorizada');
            diagnostico.recomendaciones.push('   2. Registre el certificado en SIFEN (https://sifen.set.gov.py)');
            diagnostico.recomendaciones.push('   3. Coloque el certificado en una de las rutas esperadas');
            diagnostico.recomendaciones.push('   4. Configure la variable CERT_PASSWORD con la contraseña');
            diagnostico.recomendaciones.push('   5. Reinicie el servicio Node.js');
        }
        
        console.log('✅ Diagnóstico completado');
        
        res.json({
            success: true,
            diagnostico: diagnostico
        });
        
    } catch (error) {
        console.error('❌ Error durante el diagnóstico:', error);
        res.status(500).json({
            success: false,
            message: `Error durante el diagnóstico: ${error.message}`,
            error: error.message
        });
    }
});

/**
 * Helper function para cargar certificados de forma consistente en todos los endpoints
 * 
 * @param {Object} options Opciones existentes
 * @param {string} contexto Contexto donde se está cargando (para logs)
 * @returns {Promise<Object>} Opciones actualizadas con certificado
 */
async function cargarCertificadoHelper(options = {}, contexto = 'operación') {
    if (!options.certificado || !options.clave) {
        console.log(`Cargando certificado para ${contexto} usando el servicio centralizado...`);
        
        const opcionesConCertificado = await certificadosService.prepararOpcionesConCertificado(
            options, 
            options.claveCertificado
        );
        
        Object.assign(options, opcionesConCertificado);
        
        if (options.certificado) {
            console.log(`Certificado cargado correctamente para ${contexto}`);
        } else {
            console.warn(`No se encontró certificado digital para ${contexto}. La conexión con SIFEN puede fallar.`);
        }
    }
    
    return options;
}