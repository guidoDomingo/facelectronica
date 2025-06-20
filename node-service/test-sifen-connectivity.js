/**
 * Test de conectividad básica con SIFEN
 * Prueba acceso a los WSDLs sin autenticación para diagnosticar problemas de red
 */

const soap = require('soap');
const https = require('https');

async function testWSDLAccess() {
    console.log('🔍 DIAGNÓSTICO DE CONECTIVIDAD SIFEN');
    console.log('=====================================\n');
    
    const urlsToTest = [
        {
            name: 'SIFEN TEST - Consultas',
            url: 'https://sifen-test.set.gov.py/de/ws/consultas-services.wsdl'
        },
        {
            name: 'SIFEN TEST - Recepción',
            url: 'https://sifen-test.set.gov.py/de/ws/sync-services.wsdl'
        }
    ];
    
    for (const test of urlsToTest) {
        console.log(`📡 Probando: ${test.name}`);
        console.log(`🌐 URL: ${test.url}`);
        
        try {
            // Primero probar con HTTP request básico
            console.log('   ⏳ Probando conectividad HTTP básica...');
            
            const response = await new Promise((resolve, reject) => {
                const req = https.get(test.url, (res) => {
                    let data = '';
                    res.on('data', chunk => data += chunk);
                    res.on('end', () => resolve({ status: res.statusCode, data, headers: res.headers }));
                }).on('error', reject);
                
                req.setTimeout(10000, () => {
                    req.abort();
                    reject(new Error('Timeout'));
                });
            });
            
            console.log(`   ✅ Respuesta HTTP: ${response.status}`);
            
            // Verificar si la respuesta es HTML (página de login) o XML (WSDL)
            if (response.data.trim().startsWith('<?xml')) {
                console.log('   ✅ Respuesta es XML (WSDL válido)');
                
                // Probar crear cliente SOAP sin certificados
                try {
                    console.log('   ⏳ Probando creación de cliente SOAP...');
                    const client = await soap.createClientAsync(test.url, {
                        disableCache: true,
                        timeout: 10000
                    });
                    console.log('   ✅ Cliente SOAP creado exitosamente');
                    console.log(`   📋 Métodos disponibles: ${Object.keys(client).filter(k => typeof client[k] === 'function').slice(0, 3).join(', ')}...`);
                } catch (soapError) {
                    console.log(`   ⚠️  Error al crear cliente SOAP: ${soapError.message}`);
                }
            } else if (response.data.includes('<html>') || response.data.includes('<HTML>')) {
                console.log('   ❌ Respuesta es HTML - requiere autenticación');
                console.log('   💡 Esto indica que SIFEN está funcionando pero requiere certificado válido');
            } else {
                console.log('   ❓ Respuesta desconocida');
                console.log(`   📄 Primeros 200 caracteres: ${response.data.substring(0, 200)}`);
            }
            
        } catch (error) {
            console.log(`   ❌ Error de conectividad: ${error.message}`);
            
            if (error.message.includes('ENOTFOUND')) {
                console.log('   💡 Error DNS - No se puede resolver el nombre del servidor');
            } else if (error.message.includes('ECONNREFUSED')) {
                console.log('   💡 Conexión rechazada - El servidor no está disponible');
            } else if (error.message.includes('Timeout')) {
                console.log('   💡 Timeout - El servidor está muy lento o no responde');
            }
        }
        
        console.log('');
    }
    
    console.log('🎯 CONCLUSIONES:');
    console.log('===============');
    console.log('1. Si ve "Respuesta es XML", SIFEN está accesible sin certificados');
    console.log('2. Si ve "Respuesta es HTML", SIFEN requiere certificado válido');
    console.log('3. Si ve errores de conectividad, hay problemas de red/DNS');
    console.log('4. Para usar SIFEN necesita un certificado digital autorizado por SET\n');
}

// Ejecutar test
testWSDLAccess().catch(error => {
    console.error('Error en test de conectividad:', error);
});
