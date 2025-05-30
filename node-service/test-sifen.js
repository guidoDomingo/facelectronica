/**
 * Script para probar la integración con SIFEN
 * 
 * Este script prueba tanto la consulta como el envío de documentos a SIFEN
 * utilizando la biblioteca facturacionelectronicapy-xmlgen
 */

const facturacionService = require('./services/facturacion-electronica.service');
const sifenService = require('./services/sifen.service');

async function runTests() {
    console.log('=== PRUEBA DE INTEGRACIÓN CON SIFEN ===');

    // 1. Probar la consulta de estado
    try {
        console.log('\n> Probando consultarEstadoDocumento:');
        const cdcDePrueba = '01800695631001001000000612022021816952191';
        
        console.log(`Consultando CDC: ${cdcDePrueba}`);
        const resultadoConsulta = await sifenService.consultarEstadoDocumento(
            cdcDePrueba, 
            'test', 
            { simular: true }
        );
        
        console.log('Resultado de consulta:');
        console.log(JSON.stringify(resultadoConsulta, null, 2));
    } catch (error) {
        console.error('Error en prueba de consulta:', error);
    }

    // 2. Probar la generación y envío de un XML
    try {
        console.log('\n> Probando generación de XML y envío:');
        
        // Datos de ejemplo para generar un XML
        const params = {
            "version": 150,
            "fechaFirmaDigital": "2023-12-01T07:30:00",
            "ruc": "80069563-1",
            "razonSocial": "Empresa de Ejemplo S.A.",
            "nombreFantasia": "Empresa de Ejemplo",
            "actividadesEconomicas": [
                {
                    "codigo": "46510",
                    "descripcion": "VENTA MAYORISTA DE COMPUTADORAS, EQUIPOS"
                }
            ],
            "timbradoNumero": "12558946",
            "timbradoFecha": "2023-01-01",
            "tipoContribuyente": 2,
            "tipoRegimen": 8,
            "establecimiento": {
                "codigo": "001",
                "direccion": "Dirección de prueba 123",
                "numeroCasa": "123",
                "complementoDireccion1": "Edificio Ejemplo",
                "complementoDireccion2": "Piso 2",
                "departamento": 11,
                "departamentoDescripcion": "ALTO PARANA",
                "distrito": 143,
                "distritoDescripcion": "CIUDAD DEL ESTE",
                "ciudad": 4518,
                "ciudadDescripcion": "CIUDAD DEL ESTE",
                "telefono": "0985123456",
                "email": "info@empresa.com",
                "denominacion": "Casa Central"
            }
        };

        const data = {
            "tipoDocumento": 1,
            "establecimiento": "001",
            "punto": "001",
            "numero": "0000001",
            "fecha": "2023-12-01T08:00:00",
            "tipoEmision": 1,
            "tipoTransaccion": 1,
            "moneda": "PYG",
            "cliente": {
                "contribuyente": true,
                "ruc": "80069675-1",
                "razonSocial": "Cliente de Ejemplo S.A.",
                "nombreFantasia": "Cliente de Ejemplo",
                "direccion": "Calle Principal 456",
                "numeroCasa": "456",
                "departamento": 11,
                "departamentoDescripcion": "ALTO PARANA",
                "distrito": 143,
                "distritoDescripcion": "CIUDAD DEL ESTE",
                "ciudad": 4518,
                "ciudadDescripcion": "CIUDAD DEL ESTE",
                "telefono": "0985456789",
                "email": "cliente@ejemplo.com"
            },
            "usuario": "usuario-prueba",
            "factura": {
                "presencia": 1
            },
            "condicion": {
                "tipo": 1,
                "entregas": [
                    {
                        "tipo": 1,
                        "monto": 1210000,
                        "moneda": "PYG"
                    }
                ]
            },
            "items": [
                {
                    "codigo": "001",
                    "descripcion": "Producto de prueba",
                    "cantidad": 1,
                    "precioUnitario": 1000000,
                    "iva": 10,
                    "ivaTipo": 1,
                    "unidadMedida": 77,
                    "ivaBase": 100
                }
            ]
        };

        console.log('Generando XML...');
        const xmlGenerado = await facturacionService.generateXML(params, data, { testing: true });
        
        console.log(`XML generado (${xmlGenerado.length} bytes)`);
        
        // Ahora probamos el envío (simulado)
        console.log('Enviando XML a SIFEN...');
        const resultadoEnvio = await sifenService.enviarDocumento(
            xmlGenerado, 
            'test',
            { simular: true }
        );
        
        console.log('Resultado de envío:');
        console.log(JSON.stringify(resultadoEnvio, null, 2));
    } catch (error) {
        console.error('Error en prueba de envío:', error);
    }

    console.log('\n=== PRUEBAS COMPLETADAS ===');
}

// Ejecutar las pruebas
runTests().catch(error => {
    console.error('Error en las pruebas:', error);
});
