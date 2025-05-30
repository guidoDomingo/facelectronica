/**
 * Servicio para interactuar con SIFEN (Sistema Integrado de Facturación Electrónica Nacional de Paraguay)
 */

// Importar la biblioteca de generación de XML
const xmlgenModule = require('facturacionelectronicapy-xmlgen');
const xmlgen = xmlgenModule.default;

// Importar el cliente SOAP para comunicación directa con SIFEN
const soap = require('soap');
const fs = require('fs');
const path = require('path');
const { DOMParser } = require('xmldom');

/**
 * Consulta el estado de un documento electrónico en SIFEN por su CDC
 * 
 * @param {string} cdc - Código de Control del documento a consultar
 * @param {string} ambiente - Ambiente de SIFEN ('test' o 'prod')
 * @param {object} options - Opciones adicionales
 * @returns {Promise<object>} - Respuesta de SIFEN
 */
async function consultarEstadoDocumento(cdc, ambiente = 'test', options = {}) {
    try {
        console.log(`Consultando estado de documento con CDC: ${cdc} en ambiente: ${ambiente}`);
        
        // Determinar la URL del servicio de consultas según el ambiente
        const url = ambiente === 'test' 
            ? 'https://sifen-test.set.gov.py/de/ws/consultas-services.wsdl'
            : 'https://sifen.set.gov.py/de/ws/consultas-services.wsdl';
            
        try {
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
            
            console.log(`Creando cliente SOAP para ${url}`);
            
            // Opciones del cliente SOAP para manejar correctamente la autenticación y los timeouts
            const clientOptions = {
                disableCache: true,
                forceSoap12Headers: false,
                endpoint: url,
                timeout: 30000,  // 30 segundos de timeout
                wsdl_headers: {
                    connection: 'keep-alive',
                    'user-agent': 'Node-SOAP/1.1.11',
                    accept: 'text/html,application/xhtml+xml,application/xml,text/xml;q=0.9,*/*;q=0.8',
                    'accept-encoding': 'gzip,deflate',
                    'accept-charset': 'utf-8'
                },
                wsdl_options: {
                    forever: true
                }
            };
            
            // Si se proporcionaron certificados, configurarlos
            if (options.certificado && options.clave) {
                clientOptions.wsdl_options.cert = options.certificado;
                clientOptions.wsdl_options.key = options.clave;
                clientOptions.wsdl_options.rejectUnauthorized = false;
            }
            
            try {
                // Crear cliente SOAP para comunicarse con SIFEN
                const client = await soap.createClientAsync(url, clientOptions);
                
                // Configurar los datos para la consulta según el formato esperado por SIFEN
                const consultaParams = {
                    dId: cdc
                };
                
                console.log(`Enviando consulta a ${url} para CDC: ${cdc}`);
                
                // Realizar la consulta real al servicio web de SIFEN
                // La función correcta según el WSDL de SIFEN es "rConsultaDE"
                const resultado = await client.rConsultaDEAsync({ rConsultaDE: consultaParams });
                
                // Los servicios SOAP de SIFEN devuelven un array donde el primer elemento es el resultado
                const respuestaSIFEN = resultado[0] || {};
                
                console.log('Respuesta de consulta recibida:', JSON.stringify(respuestaSIFEN));
                
                // Extraer información relevante de la respuesta SOAP
                const estado = respuestaSIFEN.dEstado || respuestaSIFEN.estado || 'Desconocido';
                const codigo = respuestaSIFEN.dCodRes || respuestaSIFEN.codigoRespuesta || '999';
                const mensaje = respuestaSIFEN.dMsgRes || respuestaSIFEN.mensajeRespuesta || 'No hay información adicional';
                const fechaProceso = respuestaSIFEN.dFecProc || new Date().toISOString();
                
                // Transformar la respuesta al formato esperado por nuestro sistema
                const response = {
                    estado: 'real',
                    respuesta: {
                        estado: estado,
                        codigo: codigo,
                        mensaje: mensaje,
                        fechaProceso: fechaProceso,
                        respuestaCompleta: respuestaSIFEN  // Incluimos toda la respuesta para debug
                    }
                };
                
                return response;            } catch (soapError) {
                console.error('❌ Error SOAP al consultar estado:', soapError.message);
                
                // Manejo específico de errores de autenticación
                if (soapError.message.includes('authentication') || 
                    soapError.message.includes('<html>') ||
                    soapError.message.includes('Root element of WSDL was <html>')) {
                    
                    console.error('❌ ERROR DE AUTENTICACIÓN CON SIFEN');
                    console.error('   Posibles causas:');
                    console.error('   1. Certificado digital no válido o expirado');
                    console.error('   2. Contraseña del certificado incorrecta');
                    console.error('   3. Certificado no autorizado para SIFEN');
                    console.error('   4. Problemas de red o firewall');
                    
                    return {
                        estado: 'error',
                        respuesta: {
                            estado: 'Error de autenticación',
                            codigo: 'AUTH-001',
                            mensaje: 'Error de autenticación con SIFEN. El servidor devolvió HTML en lugar del WSDL esperado.',
                            fechaProceso: new Date().toISOString(),
                            detalleError: soapError.message,
                            solucion: 'Verifique que el certificado digital sea válido, esté vigente y esté autorizado para SIFEN'
                        }
                    };
                }
                
                // Errores de conexión/red
                if (soapError.message.includes('ENOTFOUND') || 
                    soapError.message.includes('ECONNREFUSED') ||
                    soapError.message.includes('timeout')) {
                    
                    console.error('❌ ERROR DE CONEXIÓN CON SIFEN');
                    return {
                        estado: 'error',
                        respuesta: {
                            estado: 'Error de conexión',
                            codigo: 'NET-001',
                            mensaje: 'No se pudo conectar con el servidor de SIFEN. Verifique su conexión a internet.',
                            fechaProceso: new Date().toISOString(),
                            detalleError: soapError.message
                        }
                    };
                }
                
                // Otros errores SOAP
                console.error('❌ Error SOAP genérico:', soapError);
                return {
                    estado: 'error',
                    respuesta: {
                        estado: 'Error SOAP',
                        codigo: 'SOAP-001',
                        mensaje: soapError.message || 'Error en la comunicación SOAP con SIFEN',
                        fechaProceso: new Date().toISOString(),
                        detalleError: soapError.message
                    }
                };
            }
        } catch (error) {
            console.error('Error al consultar estado de documento:', error);
            // En caso de error, devolvemos una respuesta con el error para que sea manejable
            return {
                estado: 'error',
                respuesta: {
                    estado: 'Error',
                    codigo: '999',
                    mensaje: error.message || 'Error desconocido al consultar estado',
                    fechaProceso: new Date().toISOString(),
                }
            };
        }
    } catch (error) {
        console.error('Error en consultarEstadoDocumento:', error);
        throw error;
    }
}

/**
 * Envía un documento electrónico a SIFEN
 * 
 * @param {string} xml - XML firmado a enviar
 * @param {string} ambiente - Ambiente de SIFEN ('test' o 'prod')
 * @param {object} options - Opciones adicionales
 * @returns {Promise<object>} - Respuesta de SIFEN
 */
async function enviarDocumento(xml, ambiente = 'test', options = {}) {
    try {
        console.log(`Enviando documento a SIFEN en ambiente: ${ambiente}`);
        
        // Determinar la URL del servicio de recepción según el ambiente
        const url = ambiente === 'test' 
            ? 'https://sifen-test.set.gov.py/de/ws/sync-services.wsdl'
            : 'https://sifen.set.gov.py/de/ws/sync-services.wsdl';
            
        try {
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
            
            console.log(`Creando cliente SOAP para ${url}`);
            
            // Opciones del cliente SOAP para manejar correctamente la autenticación y los timeouts
            const clientOptions = {
                disableCache: true,
                forceSoap12Headers: false,
                endpoint: url,
                timeout: 60000,  // 60 segundos de timeout para envío de documentos
                wsdl_headers: {
                    connection: 'keep-alive',
                    'user-agent': 'Node-SOAP/1.1.11',
                    accept: 'text/html,application/xhtml+xml,application/xml,text/xml;q=0.9,*/*;q=0.8',
                    'accept-encoding': 'gzip,deflate',
                    'accept-charset': 'utf-8'
                },
                wsdl_options: {
                    forever: true
                }
            };
            
            // Si se proporcionaron certificados, configurarlos
            if (options.certificado && options.clave) {
                clientOptions.wsdl_options.cert = options.certificado;
                clientOptions.wsdl_options.key = options.clave;
                clientOptions.wsdl_options.rejectUnauthorized = false;
            }
            
            try {
                // Crear cliente SOAP para comunicarse con SIFEN
                const client = await soap.createClientAsync(url, clientOptions);
                
                // Preparar los datos para el envío según el formato esperado por SIFEN
                // El XML debe estar firmado previamente
                const datosEnvio = {
                    rEnviDe: {
                        dDVId: xml  // Documento XML firmado
                    }
                };
                
                console.log(`Enviando documento a ${url}, tamaño XML: ${xml.length} bytes`);
                
                // Realizar el envío real al servicio web de SIFEN
                // La función según el WSDL de SIFEN es "rEnviDe"
                const resultado = await client.rEnviDeAsync(datosEnvio);
                
                // Los servicios SOAP de SIFEN devuelven un array donde el primer elemento es el resultado
                const respuestaSIFEN = resultado[0] || {};
                
                console.log('Respuesta de envío recibida:', JSON.stringify(respuestaSIFEN));
                
                // Extraer información relevante de la respuesta SOAP
                const estado = respuestaSIFEN.dEstado || respuestaSIFEN.estado || 'Desconocido';
                const codigo = respuestaSIFEN.dCodRes || respuestaSIFEN.codigoRespuesta || '999';
                const mensaje = respuestaSIFEN.dMsgRes || respuestaSIFEN.mensajeRespuesta || 'No hay información adicional';
                const fechaProceso = respuestaSIFEN.dFecProc || new Date().toISOString();
                
                // Transformar la respuesta al formato esperado por nuestro sistema
                const response = {
                    estado: 'real',
                    recepcion: {
                        estado: estado,
                        codigo: codigo,
                        mensaje: mensaje,
                        fechaProceso: fechaProceso,
                        cdc: cdc,
                        respuestaCompleta: respuestaSIFEN  // Incluimos toda la respuesta para debug
                    }
                };
                
                return response;
            } catch (soapError) {
                // Si el error contiene "authentication", probablemente es un problema de certificados
                if (soapError.message.includes('authentication') || soapError.message.includes('<html>')) {
                    console.error('Error de autenticación con SIFEN. Verifique los certificados.');
                    return {
                        estado: 'error',
                        recepcion: {
                            estado: 'Error de autenticación',
                            codigo: 'AUTH-001',
                            mensaje: 'Error de autenticación con SIFEN. Se requieren certificados válidos.',
                            fechaProceso: new Date().toISOString(),
                            cdc: cdc
                        }
                    };
                }
                
                // Otros errores SOAP
                console.error('Error SOAP al enviar documento:', soapError);
                return {
                    estado: 'error',
                    recepcion: {
                        estado: 'Error SOAP',
                        codigo: 'SOAP-001',
                        mensaje: soapError.message || 'Error en la comunicación SOAP',
                        fechaProceso: new Date().toISOString(),
                        cdc: cdc
                    }
                };
            }
        } catch (error) {
            console.error('Error al enviar documento a SIFEN:', error);
            // En caso de error, devolvemos una respuesta con el error para que sea manejable
            return {
                estado: 'error',
                recepcion: {
                    estado: 'Error',
                    codigo: '999',
                    mensaje: error.message || 'Error desconocido al enviar documento',
                    fechaProceso: new Date().toISOString(),
                    cdc: cdc
                }
            };
        }
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
