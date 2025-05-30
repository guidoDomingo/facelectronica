/**
 * Servicio para gestionar certificados digitales para autenticación con SIFEN
 */

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

/**
 * Valida si un certificado es válido y accesible
 * 
 * @param {Buffer} certificadoBuffer Buffer del certificado
 * @param {string} clave Clave del certificado
 * @returns {Object} Resultado de la validación
 */
function validarCertificado(certificadoBuffer, clave) {
    try {
        // Verificar que el buffer no esté vacío
        if (!certificadoBuffer || certificadoBuffer.length === 0) {
            return {
                valido: false,
                error: 'Certificado vacío o inválido'
            };
        }

        // Verificar que tenga el formato PKCS#12 (comienza con ciertos bytes)
        // Los archivos P12 típicamente comienzan con 0x30 (ASN.1 SEQUENCE)
        if (certificadoBuffer[0] !== 0x30) {
            return {
                valido: false,
                error: 'El archivo no parece ser un certificado PKCS#12 válido'
            };
        }

        // TODO: Agregar validación más profunda del certificado si es necesario
        // Por ahora, consideramos válido si tiene el formato básico correcto

        return {
            valido: true,
            tamaño: certificadoBuffer.length,
            mensaje: 'Certificado validado correctamente'
        };
    } catch (error) {
        return {
            valido: false,
            error: `Error al validar certificado: ${error.message}`
        };
    }
}

/**
 * Busca y carga un certificado desde las ubicaciones comunes
 * 
 * @param {string} clavePersonalizada Clave personalizada para el certificado
 * @returns {Object} Objeto con información del certificado
 */
async function cargarCertificado(clavePersonalizada = null) {
    try {
        // Posibles ubicaciones del certificado
        const ubicacionesCertificado = [
            path.join(__dirname, '..', 'certificado.p12'),
            path.join(__dirname, '..', 'cert', 'certificado.p12'),
            path.join(__dirname, '..', '..', 'storage', 'app', 'certificados', 'certificado.p12')
        ];
        
        let certificadoPath = null;
        let certificadoBuffer = null;
        
        // Buscar el certificado en las ubicaciones posibles
        for (const ubicacion of ubicacionesCertificado) {
            try {
                if (fs.existsSync(ubicacion)) {
                    certificadoPath = ubicacion;
                    certificadoBuffer = fs.readFileSync(ubicacion);
                    break;
                }
            } catch (err) {
                console.log(`No se encontró certificado en ${ubicacion}`);
            }
        }
        
        if (!certificadoBuffer) {
            console.warn('No se encontró ningún certificado disponible');
            return { 
                encontrado: false,
                mensaje: 'No se encontró ningún certificado disponible',
                certificado: null,
                ruta: null,
                clave: null
            };
        }
          console.log(`Certificado cargado desde: ${certificadoPath}`);
        
        // Determinar la clave del certificado
        // Prioridad: 1) clave personalizada, 2) variable de entorno, 3) valor por defecto
        const clave = clavePersonalizada || process.env.CERT_PASSWORD || 'clave_certificado';
        
        // Validar el certificado
        const validacion = validarCertificado(certificadoBuffer, clave);
        if (!validacion.valido) {
            console.error('Certificado inválido:', validacion.error);
            return {
                encontrado: false,
                mensaje: `Certificado encontrado pero inválido: ${validacion.error}`,
                certificado: null,
                ruta: certificadoPath,
                clave: null,
                error: validacion.error
            };
        }
        
        console.log(`Certificado validado correctamente: ${validacion.mensaje}`);
        
        return {
            encontrado: true,
            mensaje: `Certificado cargado y validado desde ${certificadoPath}`,
            certificado: certificadoBuffer,
            ruta: certificadoPath,
            clave: clave,
            validacion: validacion
        };
    } catch (error) {
        console.error('Error al cargar certificado:', error);
        return {
            encontrado: false,
            mensaje: `Error al cargar certificado: ${error.message}`,
            error: error,
            certificado: null,
            ruta: null,
            clave: null
        };
    }
}

/**
 * Prepara las opciones para incluir certificados
 * 
 * @param {Object} options Opciones existentes
 * @param {string} clavePersonalizada Clave personalizada para el certificado
 * @returns {Promise<Object>} Opciones actualizadas con certificados
 */
async function prepararOpcionesConCertificado(options = {}, clavePersonalizada = null) {
    // Si ya hay certificado en las opciones, no hacer nada
    if (options.certificado && options.clave) {
        return options;
    }
    
    // Cargar certificado
    const resultado = await cargarCertificado(clavePersonalizada);
    
    // Si se encontró el certificado, agregarlo a las opciones
    if (resultado.encontrado) {
        return {
            ...options,
            certificadoPath: resultado.ruta,
            certificado: resultado.certificado,
            clave: resultado.clave
        };
    }
    
    // Si no se encontró, devolver las opciones originales
    return options;
}

/**
 * Obtiene información detallada sobre el estado de los certificados
 * 
 * @returns {Object} Información sobre certificados disponibles
 */
async function obtenerEstadoCertificados() {
    try {
        const ubicacionesCertificado = [
            path.join(__dirname, '..', 'certificado.p12'),
            path.join(__dirname, '..', 'cert', 'certificado.p12'),
            path.join(__dirname, '..', '..', 'storage', 'app', 'certificados', 'certificado.p12')
        ];
        
        const certificadosEncontrados = [];
        
        for (const ubicacion of ubicacionesCertificado) {
            try {
                if (fs.existsSync(ubicacion)) {
                    const stats = fs.statSync(ubicacion);
                    const buffer = fs.readFileSync(ubicacion);
                    const validacion = validarCertificado(buffer);
                    
                    certificadosEncontrados.push({
                        ruta: ubicacion,
                        tamaño: stats.size,
                        fechaModificacion: stats.mtime,
                        valido: validacion.valido,
                        error: validacion.error || null
                    });
                }
            } catch (err) {
                certificadosEncontrados.push({
                    ruta: ubicacion,
                    error: `Error al acceder: ${err.message}`,
                    valido: false
                });
            }
        }
        
        return {
            total: certificadosEncontrados.length,
            certificados: certificadosEncontrados,
            claveConfigurada: !!process.env.CERT_PASSWORD
        };
    } catch (error) {
        return {
            error: `Error al obtener estado de certificados: ${error.message}`,
            total: 0,
            certificados: []
        };
    }
}

module.exports = {
    cargarCertificado,
    prepararOpcionesConCertificado,
    validarCertificado,
    obtenerEstadoCertificados
};
