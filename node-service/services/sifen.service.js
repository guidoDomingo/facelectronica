/**
 * Servicio para interactuar con SIFEN (Sistema Integrado de Facturación Electrónica Nacional de Paraguay)
 * Versión mejorada con manejo robusto de errores SOAP WSDL
 */

// Importar la biblioteca de generación de XML
const xmlgenModule = require('facturacionelectronicapy-xmlgen');
const xmlgen = xmlgenModule.default;

// Importar el cliente SOAP para comunicación directa con SIFEN
const soap = require('soap');
const fs = require('fs');
const path = require('path');
const { DOMParser } = require('xmldom');

// Importar el servicio robusto para manejo de errores WSDL
const sifenRobusto = require('./sifen-robusto.service');

/**
 * Consulta el estado de un documento electrónico en SIFEN por su CDC
 * Versión mejorada con manejo robusto de errores SOAP WSDL
 * 
 * @param {string} cdc - Código de Control del documento a consultar
 * @param {string} ambiente - Ambiente de SIFEN ('test' o 'prod')
 * @param {object} options - Opciones adicionales
 * @returns {Promise<object>} - Respuesta de SIFEN
 */
async function consultarEstadoDocumento(cdc, ambiente = 'test', options = {}) {
    try {
        console.log(`Consultando estado de documento con CDC: ${cdc} en ambiente: ${ambiente}`);
        
        // No permitimos simulación, siempre usamos la conexión real a SIFEN-Test
        if (options.simular) {
            console.log('Ignorando opción de simulación, usando conexión real a SIFEN-Test');
            options.simular = false;
        }
        
        // Verificar si se proporcionó información de certificados
        if (!options.certificado || !options.clave) {
            console.warn('⚠️  ADVERTENCIA: No se proporcionaron certificados para la autenticación con SIFEN');
            console.warn('⚠️  La consulta probablemente fallará sin certificados válidos');
            
            return {
                estado: 'error',
                respuesta: {
                    estado: 'Error de configuración',
                    codigo: 'CERT-001',
                    mensaje: 'No se encontraron certificados digitales. Configure un certificado válido para autenticarse con SIFEN.',
                    fechaProceso: new Date().toISOString(),
                    solucion: 'Coloque un certificado válido en las rutas: ./certificado.p12, ./cert/certificado.p12 o ../storage/app/certificados/certificado.p12'
                }
            };
        }
        
        console.log('✅ Certificados disponibles para autenticación con SIFEN');
        
        // Usar el servicio robusto para manejar errores WSDL
        const resultado = await sifenRobusto.consultarEstadoDocumentoRobusto(cdc, ambiente, options);
        
        return resultado;
        
    } catch (error) {
        console.error('Error en consultarEstadoDocumento:', error);
        throw error;
    }
}

/**
 * Envía un documento electrónico a SIFEN
 * Versión mejorada con manejo robusto de errores SOAP WSDL
 * 
 * @param {string} xml - XML firmado a enviar
 * @param {string} ambiente - Ambiente de SIFEN ('test' o 'prod')
 * @param {object} options - Opciones adicionales
 * @returns {Promise<object>} - Respuesta de SIFEN
 */
async function enviarDocumento(xml, ambiente = 'test', options = {}) {
    try {
        console.log(`Enviando documento a SIFEN en ambiente: ${ambiente}`);
        
        // Extraer el CDC del XML si existe
        let cdc = 'CDC-No-Encontrado';
        if (xml.includes('<dId>')) {
            const match = xml.match(/<dId>(.*?)<\/dId>/);
            if (match && match[1]) {
                cdc = match[1];
            }
        }
        
        // No permitimos simulación, siempre usamos la conexión real a SIFEN-Test
        if (options.simular) {
            console.log('Ignorando opción de simulación, usando conexión real a SIFEN-Test');
            options.simular = false;
        }
        
        // Verificar si se proporcionó información de certificados
        if (!options.certificado || !options.clave) {
            console.warn('No se proporcionaron certificados para la autenticación con SIFEN');
            console.warn('Continuando con el envío sin certificados, pero probablemente fallará');
        }
        
        // Usar el servicio robusto para manejar errores WSDL
        const resultado = await sifenRobusto.enviarDocumentoRobusto(xml, ambiente, options);
        
        return resultado;
        
    } catch (error) {
        console.error('Error en enviarDocumento:', error);
        throw error;
    }
}

// Imprimir información de depuración para verificar qué funciones están disponibles
console.log('Exportando las siguientes funciones de sifen.service.js:');
console.log('consultarEstadoDocumento:', typeof consultarEstadoDocumento);
console.log('enviarDocumento:', typeof enviarDocumento);

// Exportar las funciones
const serviceExports = {
    consultarEstadoDocumento: consultarEstadoDocumento,
    enviarDocumento: enviarDocumento
};

// Verificar lo que estamos exportando
console.log('Objeto exportado:', Object.keys(serviceExports));

module.exports = serviceExports;
