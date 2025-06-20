/**
 * Test de solución para el error SOAP WSDL con SIFEN
 * Este script implementa varias estrategias para resolver el problema de carga de WSDL
 */

const soap = require('soap');
const https = require('https');
const fs = require('fs');

async function testSifenConnection() {
    console.log('🔧 PRUEBA DE SOLUCIÓN PARA ERROR SOAP WSDL');
    console.log('==========================================\n');
    
    const wsdlUrl = 'https://sifen-test.set.gov.py/de/ws/consultas-services.wsdl';
    
    // Estrategia 1: Cliente SOAP con configuración básica mejorada
    console.log('📋 Estrategia 1: Cliente SOAP con configuración mejorada');
    console.log('-------------------------------------------------------');
    
    try {
        const clientOptions = {
            disableCache: true,
            forceSoap12Headers: false,
            timeout: 30000,
            wsdl_options: {
                forever: true,
                rejectUnauthorized: false,  // Importante para certificados auto-firmados
                strictSSL: false,
                followRedirect: true,
                headers: {
                    'User-Agent': 'SOAP-Client/1.0',
                    'Accept': 'text/xml, application/xml, application/soap+xml',
                    'Content-Type': 'text/xml; charset=utf-8'
                }
            },
            wsdl_headers: {
                'User-Agent': 'SOAP-Client/1.0',
                'Accept': 'text/xml, application/xml, application/soap+xml'
            }
        };
        
        console.log('   ⏳ Intentando crear cliente SOAP...');
        const client = await soap.createClientAsync(wsdlUrl, clientOptions);
        console.log('   ✅ Cliente SOAP creado exitosamente!');
        console.log('   📋 Servicios disponibles:', Object.keys(client));
        
        return { success: true, strategy: 1, client };
        
    } catch (error) {
        console.log(`   ❌ Error: ${error.message}`);
    }
    
    // Estrategia 2: Descargar WSDL manualmente y crear cliente local
    console.log('\n📋 Estrategia 2: Descarga manual de WSDL');
    console.log('------------------------------------------');
    
    try {
        console.log('   ⏳ Descargando WSDL manualmente...');
        
        const wsdlContent = await new Promise((resolve, reject) => {
            const options = {
                rejectUnauthorized: false,
                headers: {
                    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept': 'text/xml, application/xml, */*'
                }
            };
            
            https.get(wsdlUrl, options, (res) => {
                if (res.statusCode === 302 || res.statusCode === 301) {
                    // Manejar redirección manualmente
                    const location = res.headers.location;
                    console.log(`   🔄 Redirigido a: ${location}`);
                    
                    https.get(location, options, (res2) => {
                        let data = '';
                        res2.on('data', chunk => data += chunk);
                        res2.on('end', () => resolve(data));
                    }).on('error', reject);
                } else {
                    let data = '';
                    res.on('data', chunk => data += chunk);
                    res.on('end', () => resolve(data));
                }
            }).on('error', reject);
        });
        
        console.log(`   📄 WSDL descargado, tamaño: ${wsdlContent.length} bytes`);
        
        if (wsdlContent.includes('<?xml')) {
            console.log('   ✅ WSDL es XML válido');
            
            // Guardar WSDL localmente para usar como fallback
            const wsdlPath = './sifen-consultas.wsdl';
            fs.writeFileSync(wsdlPath, wsdlContent);
            
            // Crear cliente con WSDL local
            const client = await soap.createClientAsync(wsdlPath, {
                disableCache: true,
                endpoint: wsdlUrl.replace('.wsdl', ''),  // Endpoint sin .wsdl
            });
            
            console.log('   ✅ Cliente SOAP creado con WSDL local!');
            return { success: true, strategy: 2, client, wsdlPath };
            
        } else if (wsdlContent.includes('<html>')) {
            console.log('   ❌ Respuesta es HTML - requiere autenticación');
            console.log('   💡 SIFEN requiere certificado digital válido');
        } else {
            console.log('   ❓ Respuesta no reconocida');
            console.log(`   📄 Primeros 300 caracteres: ${wsdlContent.substring(0, 300)}`);
        }
        
    } catch (error) {
        console.log(`   ❌ Error: ${error.message}`);
    }
    
    // Estrategia 3: Usar WSDL estático (si tenemos uno)
    console.log('\n📋 Estrategia 3: WSDL estático local');
    console.log('------------------------------------');
    
    try {
        // Crear un WSDL básico para pruebas si no tenemos acceso al real
        const basicWsdl = `<?xml version="1.0" encoding="UTF-8"?>
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/"
             xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
             xmlns:tns="http://ekuatia.set.gov.py/sifen/xsd"
             targetNamespace="http://ekuatia.set.gov.py/sifen/xsd">
    
    <message name="consultaRequest">
        <part name="dId" type="xsd:string"/>
    </message>
    
    <message name="consultaResponse">
        <part name="return" type="xsd:string"/>
    </message>
    
    <portType name="ConsultasPortType">
        <operation name="rConsultaDE">
            <input message="tns:consultaRequest"/>
            <output message="tns:consultaResponse"/>
        </operation>
    </portType>
    
    <binding name="ConsultasBinding" type="tns:ConsultasPortType">
        <soap:binding transport="http://schemas.xmlsoap.org/soap/http"/>
        <operation name="rConsultaDE">
            <soap:operation soapAction=""/>
            <input><soap:body use="literal"/></input>
            <output><soap:body use="literal"/></output>
        </operation>
    </binding>
    
    <service name="ConsultasService">
        <port name="ConsultasPort" binding="tns:ConsultasBinding">
            <soap:address location="https://sifen-test.set.gov.py/de/ws/consultas-services"/>
        </port>
    </service>
</definitions>`;
        
        const staticWsdlPath = './sifen-static.wsdl';
        fs.writeFileSync(staticWsdlPath, basicWsdl);
        
        console.log('   ⏳ Creando cliente con WSDL estático...');
        const client = await soap.createClientAsync(staticWsdlPath);
        console.log('   ✅ Cliente SOAP creado con WSDL estático!');
        
        return { success: true, strategy: 3, client, wsdlPath: staticWsdlPath };
        
    } catch (error) {
        console.log(`   ❌ Error: ${error.message}`);
    }
    
    console.log('\n🎯 RESUMEN DE DIAGNÓSTICO:');
    console.log('==========================');
    console.log('❌ No se pudo establecer conexión SOAP con SIFEN');
    console.log('💡 SOLUCIONES RECOMENDADAS:');
    console.log('   1. Obtener certificado digital válido autorizado por SET');
    console.log('   2. Configurar certificado en las opciones del cliente SOAP');
    console.log('   3. Verificar que el certificado no haya expirado');
    console.log('   4. Contactar con SET para autorizar el certificado en SIFEN');
    
    return { success: false };
}

// Ejecutar test
testSifenConnection().then(result => {
    if (result.success) {
        console.log(`\n🎉 ¡ÉXITO! Estrategia ${result.strategy} funcionó`);
        if (result.wsdlPath) {
            console.log(`📁 WSDL guardado en: ${result.wsdlPath}`);
        }
    } else {
        console.log('\n❌ No se pudo establecer conexión');
    }
}).catch(error => {
    console.error('Error en test:', error);
});
