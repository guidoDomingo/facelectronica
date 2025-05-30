/**
 * Servicio para manejar eventos SIFEN (Sistema Integrado de Facturación Electrónica Nacional de Paraguay)
 */

// Importar la biblioteca 
const xmlgenModule = require('facturacionelectronicapy-xmlgen');
const xmlgen = xmlgenModule.default;

/**
 * Envía un evento de inutilización a SIFEN
 * 
 * @param {string} xml - XML del evento de inutilización firmado
 * @param {string} ambiente - Ambiente de SIFEN ('test' o 'prod')
 * @param {object} options - Opciones adicionales
 * @returns {Promise<object>} - Respuesta de SIFEN
 */
async function enviarEventoInutilizacion(xml, ambiente = 'test', options = {}) {
    try {
        console.log(`Enviando evento de inutilización a SIFEN en ambiente: ${ambiente}`);
        
        // Determinar la URL del servicio según el ambiente
        const url = ambiente === 'test' 
            ? 'https://sifen-test.set.gov.py/de/ws/eventos-services.wsdl'
            : 'https://sifen.set.gov.py/de/ws/eventos-services.wsdl';
            
        try {            // Si el módulo no tiene la funcionalidad, lanzamos un error
            if (!xmlgen.eventoInutilizacion) {
                throw new Error('La función eventoInutilizacion no está disponible en la biblioteca xmlgen');
            }
            
            // No permitimos simulación, siempre usamos la conexión real a SIFEN-Test
            if (options.simular) {
                console.log('Ignorando opción de simulación, usando conexión real a SIFEN-Test');
                options.simular = false;
            }
            
            // Extraer identificador del evento del XML si existe
            let id = 'ID-No-Encontrado';
            if (xml.includes('<Id>')) {
                const match = xml.match(/<Id>(.*?)<\/Id>/);
                if (match && match[1]) {
                    id = match[1];
                }
            }
            
            // Configurar los datos para el envío
            const datosEvento = {
                xml: xml,
                url: url
            };
            
            console.log(`Enviando evento de inutilización a ${url}, tamaño XML: ${xml.length} bytes`);
            
            // Realizar el envío real utilizando la biblioteca
            const resultado = await xmlgen.eventoInutilizacion(datosEvento);
            
            console.log('Respuesta de evento recibida:', resultado);
            
            // Transformar la respuesta al formato esperado por nuestro sistema
            const response = {
                estado: 'real',
                evento: {
                    estado: resultado.estado || 'Desconocido',
                    codigo: resultado.codigoRespuesta || resultado.respuesta?.codigo || '999',
                    mensaje: resultado.mensajeRespuesta || resultado.respuesta?.mensaje || 'No hay información adicional',
                    fechaProceso: resultado.fechaProceso || new Date().toISOString(),
                    id: id
                }
            };
            
            return response;
        } catch (error) {
            console.error('Error al enviar evento de inutilización:', error);
            // En caso de error, devolvemos una respuesta con el error para que sea manejable
            return {
                estado: 'error',
                evento: {
                    estado: 'Error',
                    codigo: '999',
                    mensaje: error.message || 'Error desconocido al enviar evento de inutilización',
                    fechaProceso: new Date().toISOString()
                }
            };
        }
    } catch (error) {
        console.error('Error en enviarEventoInutilizacion:', error);
        throw error;
    }
}

/**
 * Envía un evento de notificación a SIFEN
 * 
 * @param {string} xml - XML del evento de notificación firmado
 * @param {string} ambiente - Ambiente de SIFEN ('test' o 'prod')
 * @param {object} options - Opciones adicionales
 * @returns {Promise<object>} - Respuesta de SIFEN
 */
async function enviarEventoNotificacion(xml, ambiente = 'test', options = {}) {
    try {
        console.log(`Enviando evento de notificación a SIFEN en ambiente: ${ambiente}`);
        
        // Determinar la URL del servicio según el ambiente
        const url = ambiente === 'test' 
            ? 'https://sifen-test.set.gov.py/de/ws/eventos-services.wsdl'
            : 'https://sifen.set.gov.py/de/ws/eventos-services.wsdl';
            
        try {            // Si el módulo no tiene la funcionalidad, lanzamos un error
            if (!xmlgen.eventoNotificacion) {
                throw new Error('La función eventoNotificacion no está disponible en la biblioteca xmlgen');
            }
            
            // No permitimos simulación, siempre usamos la conexión real a SIFEN-Test
            if (options.simular) {
                console.log('Ignorando opción de simulación, usando conexión real a SIFEN-Test');
                options.simular = false;
            }
            
            // Extraer identificador del evento del XML si existe
            let id = 'ID-No-Encontrado';
            if (xml.includes('<Id>')) {
                const match = xml.match(/<Id>(.*?)<\/Id>/);
                if (match && match[1]) {
                    id = match[1];
                }
            }
            
            // Configurar los datos para el envío
            const datosEvento = {
                xml: xml,
                url: url
            };
            
            console.log(`Enviando evento de notificación a ${url}, tamaño XML: ${xml.length} bytes`);
            
            // Realizar el envío real utilizando la biblioteca
            const resultado = await xmlgen.eventoNotificacion(datosEvento);
            
            console.log('Respuesta de evento recibida:', resultado);
            
            // Transformar la respuesta al formato esperado por nuestro sistema
            const response = {
                estado: 'real',
                evento: {
                    estado: resultado.estado || 'Desconocido',
                    codigo: resultado.codigoRespuesta || resultado.respuesta?.codigo || '999',
                    mensaje: resultado.mensajeRespuesta || resultado.respuesta?.mensaje || 'No hay información adicional',
                    fechaProceso: resultado.fechaProceso || new Date().toISOString(),
                    id: id
                }
            };
            
            return response;
        } catch (error) {
            console.error('Error al enviar evento de notificación:', error);
            // En caso de error, devolvemos una respuesta con el error para que sea manejable
            return {
                estado: 'error',
                evento: {
                    estado: 'Error',
                    codigo: '999',
                    mensaje: error.message || 'Error desconocido al enviar evento de notificación',
                    fechaProceso: new Date().toISOString()
                }
            };
        }
    } catch (error) {
        console.error('Error en enviarEventoNotificacion:', error);
        throw error;
    }
}

// Exportar las funciones
const serviceExports = {
    enviarEventoInutilizacion,
    enviarEventoNotificacion
};

module.exports = serviceExports;
