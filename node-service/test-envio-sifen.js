/**
 * Script para probar el envío de documentos a SIFEN
 * 
 * Este script prueba la función de envío de un documento a SIFEN
 * utilizando el cliente SOAP implementado
 */

const fs = require('fs');
const path = require('path');
const sifenService = require('./services/sifen.service');

async function runEnvioTest() {
    try {
        console.log('Iniciando prueba de envío de documento a SIFEN...');
        
        // Intentar leer un XML de prueba
        let xmlContent;
        try {
            // Intentar leer el archivo desde la raíz del proyecto Laravel
            xmlContent = fs.readFileSync(path.join(__dirname, '..', 'factura_ejemplo.xml'), 'utf8');
        } catch (err) {
            console.error('No se pudo leer el archivo XML de prueba:', err.message);
            console.log('Usando XML de ejemplo básico para la prueba');
            
            // XML simple para probar la conexión - NO un documento válido
            xmlContent = `<?xml version="1.0" encoding="UTF-8"?>
<DE xmlns="http://ekuatia.set.gov.py/sifen/xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ekuatia.set.gov.py/sifen/xsd siRecepDE_v150.xsd">
    <dId>01800695631001001000000012023052611267896453</dId>
    <dDVId>1</dDVId>
</DE>`;
        }
        
        console.log(`XML cargado, tamaño: ${xmlContent.length} bytes`);
        
        // Forzar el uso de la conexión real a SIFEN-Test
        const options = { simular: false };
        
        // Llamar a la función de envío
        console.log('Intentando enviar documento a SIFEN...');
        const resultado = await sifenService.enviarDocumento(xmlContent, 'test', options);
        
        console.log('Resultado del envío:');
        console.log(JSON.stringify(resultado, null, 2));
        
        console.log('Prueba de envío completada.');
    } catch (error) {
        console.error('Error en la prueba de envío:', error);
    }
}

// Ejecutar la prueba
runEnvioTest().catch(error => {
    console.error('Error ejecutando la prueba:', error);
});
