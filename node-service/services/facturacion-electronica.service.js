/**
 * Servicio para interactuar con facturacionelectronicapy-xmlgen
 */

// Importar la biblioteca correctamente
const xmlgenModule = require('facturacionelectronicapy-xmlgen');
const xmlgen = xmlgenModule.default;

console.log('Biblioteca importada correctamente');
console.log('Métodos disponibles:', Object.keys(xmlgen));

/**
 * Genera un XML para el documento electrónico
 * 
 * @param {object} params - Parámetros estáticos del Contribuyente emisor
 * @param {object} data - Datos variables para el documento electrónico
 * @param {object} options - Opciones adicionales
 * @returns {Promise<string>} - XML generado
 */
async function generateXML(params, data, options = {}) {
    try {
        // Opciones por defecto
        const defaultOptions = {
            defaultValues: true,
            errorSeparator: '; ',
            errorLimit: 10,
            redondeoSedeco: true,
            decimals: 2,
            taxDecimals: 2,
            pygDecimals: 0,
            partialTaxDecimals: 8,
            pygTaxDecimals: 0,
            userObjectRemove: false,
            test: false,
        };
        
        // Combinar opciones por defecto con las recibidas
        const mergedOptions = { ...defaultOptions, ...options };
        
        // Generar XML utilizando la librería facturacionelectronicapy-xmlgen
        const xml = await xmlgen.generateXMLDE(params, data, mergedOptions);
        return xml;
    } catch (error) {
        console.error('Error al generar XML:', error);
        throw error;
    }
}

/**
 * Genera un XML para un evento de cancelación
 * 
 * @param {number} id - ID del evento
 * @param {object} params - Parámetros estáticos del Contribuyente emisor
 * @param {object} data - Datos del evento
 * @param {object} options - Opciones adicionales
 * @returns {Promise<string>} - XML generado
 */
async function generateXMLEventoCancelacion(id, params, data, options = {}) {
    try {
        const xml = await xmlgen.generateXMLEventoCancelacion(id, params, data, options);
        return xml;
    } catch (error) {
        console.error('Error al generar XML de evento de cancelación:', error);
        throw error;
    }
}

/**
 * Genera un XML para un evento de inutilización
 * 
 * @param {number} id - ID del evento
 * @param {object} params - Parámetros estáticos del Contribuyente emisor
 * @param {object} data - Datos del evento
 * @param {object} options - Opciones adicionales
 * @returns {Promise<string>} - XML generado
 */
async function generateXMLEventoInutilizacion(id, params, data, options = {}) {
    try {
        const xml = await xmlgen.generateXMLEventoInutilizacion(id, params, data, options);
        return xml;
    } catch (error) {
        console.error('Error al generar XML de evento de inutilización:', error);
        throw error;
    }
}

/**
 * Genera un XML para un evento de conformidad
 * 
 * @param {number} id - ID del evento
 * @param {object} params - Parámetros estáticos del Contribuyente emisor
 * @param {object} data - Datos del evento
 * @param {object} options - Opciones adicionales
 * @returns {Promise<string>} - XML generado
 */
async function generateXMLEventoConformidad(id, params, data, options = {}) {
    try {
        const xml = await xmlgen.generateXMLEventoConformidad(id, params, data, options);
        return xml;
    } catch (error) {
        console.error('Error al generar XML de evento de conformidad:', error);
        throw error;
    }
}

/**
 * Genera un XML para un evento de disconformidad
 * 
 * @param {number} id - ID del evento
 * @param {object} params - Parámetros estáticos del Contribuyente emisor
 * @param {object} data - Datos del evento
 * @param {object} options - Opciones adicionales
 * @returns {Promise<string>} - XML generado
 */
async function generateXMLEventoDisconformidad(id, params, data, options = {}) {
    try {
        const xml = await xmlgen.generateXMLEventoDisconformidad(id, params, data, options);
        return xml;
    } catch (error) {
        console.error('Error al generar XML de evento de disconformidad:', error);
        throw error;
    }
}

/**
 * Genera un XML para un evento de desconocimiento
 * 
 * @param {number} id - ID del evento
 * @param {object} params - Parámetros estáticos del Contribuyente emisor
 * @param {object} data - Datos del evento
 * @param {object} options - Opciones adicionales
 * @returns {Promise<string>} - XML generado
 */
async function generateXMLEventoDesconocimiento(id, params, data, options = {}) {
    try {
        const xml = await xmlgen.generateXMLEventoDesconocimiento(id, params, data, options);
        return xml;
    } catch (error) {
        console.error('Error al generar XML de evento de desconocimiento:', error);
        throw error;
    }
}

/**
 * Genera un XML para un evento de notificación
 * 
 * @param {number} id - ID del evento
 * @param {object} params - Parámetros estáticos del Contribuyente emisor
 * @param {object} data - Datos del evento
 * @param {object} options - Opciones adicionales
 * @returns {Promise<string>} - XML generado
 */
async function generateXMLEventoNotificacion(id, params, data, options = {}) {
    try {
        const xml = await xmlgen.generateXMLEventoNotificacion(id, params, data, options);
        return xml;
    } catch (error) {
        console.error('Error al generar XML de evento de notificación:', error);
        throw error;
    }
}

/**
 * Firma un XML utilizando un certificado digital
 * 
 * @param {string} xml - XML a firmar
 * @param {string} certPath - Ruta al archivo de certificado P12
 * @param {string} certPassword - Contraseña del certificado
 * @param {object} options - Opciones adicionales
 * @returns {Promise<string>} - XML firmado
 */
async function signXML(xml, certPath, certPassword, options = {}) {
    try {
        // En una implementación real, aquí se utilizaría una librería para firmar XML
        // como xml-crypto, xadesjs u otra librería compatible con las especificaciones de SIFEN
        
        // Ejemplo de comentario de implementación real:
        // 1. Cargar el certificado P12
        // 2. Extraer la clave privada y el certificado
        // 3. Crear un objeto de firma XML según las especificaciones de SIFEN
        // 4. Firmar el XML
        // 5. Devolver el XML firmado
        
        console.log('Solicitud de firma XML recibida');
        console.log(`- Certificado: ${certPath}`);
        console.log('- XML tamaño:', xml.length);
        
        // Implementación simulada (solo para desarrollo)
        // En un entorno de producción, se debe implementar la firma real
        return xml.replace('</DE>', '<Signature>FIRMA_SIMULADA</Signature></DE>');
    } catch (error) {
        console.error('Error al firmar XML:', error);
        throw error;
    }
}

/**
 * Obtiene información de una ciudad por su ID
 * 
 * @param {number} ciudadId - ID de la ciudad
 * @returns {object} - Información de la ciudad
 */
function getCiudad(ciudadId) {
    try {
        return xmlgen.getCiudad(ciudadId);
    } catch (error) {
        console.error('Error al obtener información de ciudad:', error);
        throw error;
    }
}

module.exports = {
    generateXML,
    generateXMLEventoCancelacion,
    generateXMLEventoInutilizacion,
    generateXMLEventoConformidad,
    generateXMLEventoDisconformidad,
    generateXMLEventoDesconocimiento,
    generateXMLEventoNotificacion,
    signXML,
    getCiudad,
};
