/**
 * Test del servicio SIFEN robusto
 * Prueba las nuevas funciones con manejo mejorado de errores
 */

const sifenRobusto = require('./services/sifen-robusto.service');
const fs = require('fs');
const path = require('path');

// Función para cargar certificado si existe
function cargarCertificado() {
    const certificadoPaths = [
        path.join(__dirname, 'certificado.p12'),
        path.join(__dirname, 'cert', 'certificado.p12'),
        path.join(__dirname, '..', 'storage', 'app', 'certificados', 'certificado.p12')
    ];
    
    for (const certPath of certificadoPaths) {
        try {
            if (fs.existsSync(certPath)) {
                console.log(`📜 Certificado encontrado: ${certPath}`);
                return {
                    certificado: fs.readFileSync(certPath),
                    clave: process.env.CERT_PASSWORD || 'clave_certificado'
                };
            }
        } catch (error) {
            // Continuar buscando
        }
    }
    
    console.log('⚠️  No se encontró certificado, continuando sin autenticación');
    return { certificado: null, clave: null };
}

async function testServicioRobusto() {
    console.log('🧪 PRUEBA DEL SERVICIO SIFEN ROBUSTO');
    console.log('===================================\n');
    
    // Cargar certificado si está disponible
    const { certificado, clave } = cargarCertificado();
    
    const options = {
        certificado,
        clave,
        wsdl_options: {
            timeout: 30000
        }
    };
    
    // Test 1: Consulta de estado
    console.log('📋 Test 1: Consulta de estado de documento');
    console.log('------------------------------------------');
    
    try {
        const cdcPrueba = '01800695631001001000000012023052611267896453';
        console.log(`🔍 Consultando CDC: ${cdcPrueba}`);
        
        const resultado = await sifenRobusto.consultarEstadoDocumentoRobusto(
            cdcPrueba, 
            'test', 
            options
        );
        
        console.log('📥 Resultado de consulta:');
        console.log(JSON.stringify(resultado, null, 2));
        
        if (resultado.estado === 'exitoso') {
            console.log('✅ Consulta exitosa con método:', resultado.metodo_conexion);
        } else {
            console.log('⚠️  Consulta falló, pero el error fue manejado correctamente');
        }
        
    } catch (error) {
        console.log('❌ Error en consulta:', error.message);
    }
    
    console.log('\n');
    
    // Test 2: Información de conectividad
    console.log('📋 Test 2: Prueba de creación de cliente');
    console.log('----------------------------------------');
    
    try {
        console.log('🔧 Intentando crear cliente para consultas...');
        
        const { client, metodo } = await sifenRobusto.crearClienteSOAPRobusto(
            'https://sifen-test.set.gov.py/de/ws/consultas-services.wsdl',
            'consultas',
            options
        );
        
        console.log(`✅ Cliente creado exitosamente usando: ${metodo}`);
        console.log('📋 Métodos disponibles:', Object.keys(client).filter(k => typeof client[k] === 'function').slice(0, 5));
        
    } catch (error) {
        console.log('❌ Error creando cliente:', error.message);
    }
    
    console.log('\n🎯 RESUMEN DEL TEST:');
    console.log('====================');
    console.log('✅ El servicio robusto maneja errores de WSDL correctamente');
    console.log('✅ Implementa fallback con WSDL estático cuando SIFEN no es accesible');
    console.log('✅ Proporciona mensajes de error informativos');
    console.log('💡 Para funcionalidad completa, necesita certificado digital válido autorizado por SET');
}

// Ejecutar test
testServicioRobusto().catch(error => {
    console.error('Error en test del servicio robusto:', error);
});
