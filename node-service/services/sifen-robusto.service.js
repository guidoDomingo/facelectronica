/**
 * Servicio SIFEN mejorado con manejo robusto de errores SOAP WSDL
 * Soluciona el problema "SOAP-ERROR: Parsing WSDL: Couldn't load from..."
 */

const soap = require('soap');
const fs = require('fs');
const path = require('path');
const https = require('https');

// WSDL estático como fallback para cuando no se puede acceder al WSDL de SIFEN
const WSDL_CONSULTAS_ESTATICO = `<?xml version="1.0" encoding="UTF-8"?>
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/"
             xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
             xmlns:xsd="http://www.w3.org/2001/XMLSchema"
             xmlns:tns="http://ekuatia.set.gov.py/sifen/xsd"
             targetNamespace="http://ekuatia.set.gov.py/sifen/xsd"
             elementFormDefault="qualified">

    <types>
        <xsd:schema targetNamespace="http://ekuatia.set.gov.py/sifen/xsd">
            <xsd:element name="rConsultaDE">
                <xsd:complexType>
                    <xsd:sequence>
                        <xsd:element name="dId" type="xsd:string"/>
                    </xsd:sequence>
                </xsd:complexType>
            </xsd:element>
            <xsd:element name="rConsultaDEResponse">
                <xsd:complexType>
                    <xsd:sequence>
                        <xsd:element name="dEstado" type="xsd:string"/>
                        <xsd:element name="dCodRes" type="xsd:string"/>
                        <xsd:element name="dMsgRes" type="xsd:string"/>
                        <xsd:element name="dFecProc" type="xsd:string"/>
                    </xsd:sequence>
                </xsd:complexType>
            </xsd:element>
        </xsd:schema>
    </types>

    <message name="consultaRequest">
        <part name="parameters" element="tns:rConsultaDE"/>
    </message>

    <message name="consultaResponse">
        <part name="parameters" element="tns:rConsultaDEResponse"/>
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
            <soap:operation soapAction="urn:rConsultaDE"/>
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

const WSDL_RECEPCION_ESTATICO = `<?xml version="1.0" encoding="UTF-8"?>
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/"
             xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
             xmlns:xsd="http://www.w3.org/2001/XMLSchema"
             xmlns:tns="http://ekuatia.set.gov.py/sifen/xsd"
             targetNamespace="http://ekuatia.set.gov.py/sifen/xsd"
             elementFormDefault="qualified">

    <types>
        <xsd:schema targetNamespace="http://ekuatia.set.gov.py/sifen/xsd">
            <xsd:element name="rEnviDe">
                <xsd:complexType>
                    <xsd:sequence>
                        <xsd:element name="dXml" type="xsd:string"/>
                    </xsd:sequence>
                </xsd:complexType>
            </xsd:element>
            <xsd:element name="rEnviDeResponse">
                <xsd:complexType>
                    <xsd:sequence>
                        <xsd:element name="dEstado" type="xsd:string"/>
                        <xsd:element name="dCodRes" type="xsd:string"/>
                        <xsd:element name="dMsgRes" type="xsd:string"/>
                        <xsd:element name="dFecProc" type="xsd:string"/>
                    </xsd:sequence>
                </xsd:complexType>
            </xsd:element>
        </xsd:schema>
    </types>

    <message name="envioRequest">
        <part name="parameters" element="tns:rEnviDe"/>
    </message>

    <message name="envioResponse">
        <part name="parameters" element="tns:rEnviDeResponse"/>
    </message>

    <portType name="RecepcionPortType">
        <operation name="rEnviDe">
            <input message="tns:envioRequest"/>
            <output message="tns:envioResponse"/>
        </operation>
    </portType>

    <binding name="RecepcionBinding" type="tns:RecepcionPortType">
        <soap:binding transport="http://schemas.xmlsoap.org/soap/http"/>
        <operation name="rEnviDe">
            <soap:operation soapAction="urn:rEnviDe"/>
            <input><soap:body use="literal"/></input>
            <output><soap:body use="literal"/></output>
        </operation>
    </binding>

    <service name="RecepcionService">
        <port name="RecepcionPort" binding="tns:RecepcionBinding">
            <soap:address location="https://sifen-test.set.gov.py/de/ws/sync-services"/>
        </port>
    </service>
</definitions>`;

/**
 * Crea un cliente SOAP con manejo robusto de errores y fallbacks
 */
async function crearClienteSOAPRobusto(url, tipo = 'consultas', opciones = {}) {
    console.log(`🔧 Creando cliente SOAP robusto para ${tipo}`);
    console.log(`🌐 URL objetivo: ${url}`);
    
    // Configuración base del cliente SOAP
    const clientOptions = {
        disableCache: true,
        forceSoap12Headers: false,
        timeout: 30000,
        wsdl_options: {
            forever: true,
            rejectUnauthorized: false,
            strictSSL: false,
            followRedirect: true,
            ...opciones.wsdl_options
        },
        wsdl_headers: {
            'User-Agent': 'SIFEN-Client/1.0',
            'Accept': 'text/xml, application/xml, application/soap+xml',
            'Content-Type': 'text/xml; charset=utf-8',
            ...opciones.headers
        }
    };
    
    // Agregar certificados si están disponibles
    if (opciones.certificado && opciones.clave) {
        console.log('🔐 Configurando autenticación con certificado');
        clientOptions.wsdl_options.cert = opciones.certificado;
        clientOptions.wsdl_options.key = opciones.clave;
        clientOptions.wsdl_options.passphrase = opciones.clave;
    }
    
    // Estrategia 1: Intentar acceso directo al WSDL
    try {
        console.log('📡 Estrategia 1: Acceso directo al WSDL');
        const client = await soap.createClientAsync(url, clientOptions);
        console.log('✅ Cliente SOAP creado exitosamente con WSDL remoto');
        return { client, metodo: 'wsdl_remoto', url };
    } catch (error) {
        console.log(`❌ Error acceso directo: ${error.message}`);
        
        // Si es error de autenticación, continuar con fallback
        if (error.message.includes('Root element of WSDL was <html>') || 
            error.message.includes('authentication')) {
            console.log('🔍 Error de autenticación detectado, usando WSDL estático');
        }
    }
    
    // Estrategia 2: Usar WSDL estático local
    try {
        console.log('📡 Estrategia 2: WSDL estático local');
        
        const wsdlContent = tipo === 'consultas' ? WSDL_CONSULTAS_ESTATICO : WSDL_RECEPCION_ESTATICO;
        const wsdlPath = path.join(__dirname, `sifen-${tipo}-fallback.wsdl`);
        
        // Guardar WSDL estático
        fs.writeFileSync(wsdlPath, wsdlContent);
        console.log(`💾 WSDL estático guardado en: ${wsdlPath}`);
        
        // Crear cliente con WSDL local pero endpoint remoto
        const clientOptionsLocal = {
            ...clientOptions,
            endpoint: url.replace('.wsdl', '')  // Endpoint sin .wsdl
        };
        
        const client = await soap.createClientAsync(wsdlPath, clientOptionsLocal);
        console.log('✅ Cliente SOAP creado con WSDL estático local');
        
        // Limpiar archivo temporal
        try {
            fs.unlinkSync(wsdlPath);
        } catch (e) {
            // Ignorar errores de limpieza
        }
        
        return { client, metodo: 'wsdl_estatico', url };
        
    } catch (error) {
        console.log(`❌ Error WSDL estático: ${error.message}`);
    }
    
    // Si llegamos aquí, no se pudo crear el cliente
    throw new Error(`No se pudo crear cliente SOAP para ${url}. Verifique certificados y conectividad.`);
}

/**
 * Consulta estado de documento con manejo robusto de errores
 */
async function consultarEstadoDocumentoRobusto(cdc, ambiente = 'test', options = {}) {
    try {
        console.log(`📋 Consultando estado CDC: ${cdc} en ambiente: ${ambiente}`);
        
        // Determinar URL según ambiente
        const url = ambiente === 'test' 
            ? 'https://sifen-test.set.gov.py/de/ws/consultas-services.wsdl'
            : 'https://sifen.set.gov.py/de/ws/consultas-services.wsdl';
        
        // Crear cliente robusto
        const { client, metodo } = await crearClienteSOAPRobusto(url, 'consultas', options);
        console.log(`🔧 Cliente creado usando método: ${metodo}`);
        
        // Preparar parámetros de consulta
        const consultaParams = { dId: cdc };
        
        console.log('📤 Enviando consulta a SIFEN...');
        
        // Intentar consulta
        const resultado = await client.rConsultaDEAsync({ rConsultaDE: consultaParams });
        const respuesta = resultado[0] || {};
        
        console.log('📥 Respuesta recibida de SIFEN');
        
        // Procesar respuesta
        return {
            estado: 'exitoso',
            metodo_conexion: metodo,
            respuesta: {
                estado: respuesta.dEstado || 'Desconocido',
                codigo: respuesta.dCodRes || '999',
                mensaje: respuesta.dMsgRes || 'Sin mensaje',
                fechaProceso: respuesta.dFecProc || new Date().toISOString()
            }
        };
        
    } catch (error) {
        console.error('❌ Error en consulta robusta:', error.message);
        
        // Devolver error estructurado
        return {
            estado: 'error',
            respuesta: {
                codigo: 'CONN-ERROR',
                mensaje: `Error de conectividad con SIFEN: ${error.message}`,
                fechaProceso: new Date().toISOString(),
                recomendacion: 'Verifique certificados y conectividad con SIFEN'
            }
        };
    }
}

/**
 * Envía documento con manejo robusto de errores
 */
async function enviarDocumentoRobusto(xml, ambiente = 'test', options = {}) {
    try {
        console.log(`📋 Enviando documento a SIFEN en ambiente: ${ambiente}`);
        
        // Determinar URL según ambiente
        const url = ambiente === 'test' 
            ? 'https://sifen-test.set.gov.py/de/ws/sync-services.wsdl'
            : 'https://sifen.set.gov.py/de/ws/sync-services.wsdl';
        
        // Crear cliente robusto
        const { client, metodo } = await crearClienteSOAPRobusto(url, 'recepcion', options);
        console.log(`🔧 Cliente creado usando método: ${metodo}`);
        
        // Preparar datos de envío
        const datosEnvio = { dXml: xml };
        
        console.log('📤 Enviando documento a SIFEN...');
        
        // Intentar envío
        const resultado = await client.rEnviDeAsync({ rEnviDe: datosEnvio });
        const respuesta = resultado[0] || {};
        
        console.log('📥 Respuesta recibida de SIFEN');
        
        // Procesar respuesta
        return {
            estado: 'exitoso',
            metodo_conexion: metodo,
            respuesta: {
                estado: respuesta.dEstado || 'Desconocido',
                codigo: respuesta.dCodRes || '999',
                mensaje: respuesta.dMsgRes || 'Sin mensaje',
                fechaProceso: respuesta.dFecProc || new Date().toISOString()
            }
        };
        
    } catch (error) {
        console.error('❌ Error en envío robusto:', error.message);
        
        // Devolver error estructurado
        return {
            estado: 'error',
            respuesta: {
                codigo: 'CONN-ERROR',
                mensaje: `Error de conectividad con SIFEN: ${error.message}`,
                fechaProceso: new Date().toISOString(),
                recomendacion: 'Verifique certificados y conectividad con SIFEN'
            }
        };
    }
}

module.exports = {
    consultarEstadoDocumentoRobusto,
    enviarDocumentoRobusto,
    crearClienteSOAPRobusto
};
