/**
 * Script para probar la consulta a SIFEN
 * 
 * Este script prueba la función de consulta de estado de un documento en SIFEN
 * utilizando el cliente SOAP implementado
 */

const fs = require('fs');
const path = require('path');
const sifenService = require('./services/sifen.service');

// Función para cargar un certificado si existe
function cargarCertificado() {
    try {
        // Intentar cargar el certificado desde varias ubicaciones posibles
        const ubicacionesCertificado = [
            path.join(__dirname, 'certificado.p12'),
            path.join(__dirname, 'cert', 'certificado.p12'),
            path.join(__dirname, '..', 'storage', 'app', 'certificados', 'certificado.p12')
        ];
        
        let certificadoBuffer = null;
        let ubicacionEncontrada = null;
        
        for (const ubicacion of ubicacionesCertificado) {
            try {
                if (fs.existsSync(ubicacion)) {
                    certificadoBuffer = fs.readFileSync(ubicacion);
                    ubicacionEncontrada = ubicacion;
                    break;
                }
            } catch (err) {
                console.log(`No se encontró certificado en ${ubicacion}`);
            }
        }
        
        if (!certificadoBuffer) {
            console.warn('No se encontró ningún certificado disponible');
            return { certificado: null, clave: null };
        }
        
        console.log(`Certificado cargado desde: ${ubicacionEncontrada}`);
        
        // En un entorno real, la clave del certificado debería estar protegida
        const claveCertificado = process.env.CERT_PASSWORD || 'clave_certificado';
        
        return {
            certificado: certificadoBuffer,
            clave: claveCertificado
        };
    } catch (error) {
        console.error('Error al cargar certificado:', error);
        return { certificado: null, clave: null };
    }
}

async function runConsultaTest() {
    try {
        console.log('Iniciando prueba de consulta de estado de documento en SIFEN...');
        
        // CDC de prueba - es de ejemplo y probablemente no existe en SIFEN
        const cdc = '01800695631001001000000012023052611267896453';
        
        console.log(`Consultando estado del documento con CDC: ${cdc}`);
        
        // Intentar cargar certificado
        const { certificado, clave } = cargarCertificado();
        
        // Forzar el uso de la conexión real a SIFEN-Test
        const options = { 
            simular: false,
            certificado: certificado,
            clave: clave
        };
        
        // Llamar a la función de consulta
        console.log('Enviando consulta a SIFEN...');
        const resultado = await sifenService.consultarEstadoDocumento(cdc, 'test', options);
        
        console.log('Resultado de la consulta:');
        console.log(JSON.stringify(resultado, null, 2));
        
        console.log('Prueba de consulta completada.');
    } catch (error) {
        console.error('Error en la prueba de consulta:', error);
    }
}

// Ejecutar la prueba
runConsultaTest().catch(error => {
    console.error('Error ejecutando la prueba:', error);
});
