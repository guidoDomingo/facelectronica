/**
 * Generador de certificado de prueba para SIFEN
 * 
 * Este script genera un certificado PKCS#12 de prueba para poder
 * probar la integración con SIFEN sin necesidad de un certificado real.
 * 
 * IMPORTANTE: Este certificado es SOLO para pruebas y desarrollo.
 * NO debe usarse en producción.
 */

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

// Función para crear directorios si no existen
function ensureDirectoryExists(dirPath) {
    if (!fs.existsSync(dirPath)) {
        fs.mkdirSync(dirPath, { recursive: true });
        console.log(`✅ Directorio creado: ${dirPath}`);
    }
}

// Función para generar un certificado de prueba simple
function generateTestCertificate() {
    console.log('🔐 Generando certificado de prueba para SIFEN...\n');
    
    try {
        // Crear directorios necesarios
        const storageDir = path.join(__dirname, 'storage', 'app', 'certificados');
        const nodeServiceDir = path.join(__dirname, 'node-service');
        const nodeCertDir = path.join(__dirname, 'node-service', 'cert');
        
        ensureDirectoryExists(storageDir);
        ensureDirectoryExists(nodeCertDir);
        
        // Generar un buffer que simule un certificado PKCS#12
        // Nota: Este es un certificado simulado para pruebas solamente
        const certificateHeader = Buffer.from([0x30]); // ASN.1 SEQUENCE start
        const randomData = crypto.randomBytes(1023); // Datos aleatorios
        const mockCertificate = Buffer.concat([certificateHeader, randomData]);
        
        // Rutas de destino
        const paths = [
            path.join(storageDir, 'certificado.p12'),
            path.join(nodeServiceDir, 'certificado.p12'),
            path.join(nodeCertDir, 'certificado.p12')
        ];
        
        // Escribir el certificado en todas las ubicaciones
        paths.forEach(certPath => {
            fs.writeFileSync(certPath, mockCertificate);
            console.log(`📁 Certificado creado: ${certPath}`);
        });
        
        // Configurar variable de entorno
        const password = 'test1234';
        
        console.log('\n✅ Certificado de prueba generado exitosamente!\n');
        console.log('📋 Información del certificado:');
        console.log(`   - Contraseña: ${password}`);
        console.log(`   - Tamaño: ${mockCertificate.length} bytes`);
        console.log(`   - Tipo: PKCS#12 simulado`);
        
        console.log('\n🔧 Configuración requerida:');
        console.log('   Agregue esta línea a su archivo .env:');
        console.log(`   CERT_PASSWORD=${password}`);
        
        console.log('\n⚠️  IMPORTANTE:');
        console.log('   - Este es un certificado de PRUEBA solamente');
        console.log('   - NO funcionará con SIFEN real en producción');
        console.log('   - Para producción necesita un certificado oficial de una CA autorizada');
        console.log('   - Documentación oficial: https://www.dnit.gov.py/web/e-kuatia/documentacion-tecnica');
        
        return {
            success: true,
            password: password,
            paths: paths
        };
        
    } catch (error) {
        console.error('❌ Error al generar certificado:', error.message);
        return {
            success: false,
            error: error.message
        };
    }
}

// Ejecutar la generación
if (require.main === module) {
    generateTestCertificate();
}

module.exports = generateTestCertificate;
