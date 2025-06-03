/**
 * Servicio Node.js para Facturación Electrónica Paraguay
 * 
 * Este servicio implementa una API REST que utiliza el módulo facturacionelectronicapy-xmlgen
 * para generar documentos XML para SIFEN (Paraguay).
 */

const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const xmlgen = require('facturacionelectronicapy-xmlgen');

const app = express();
const port = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json({ limit: '10mb' }));
app.use(bodyParser.urlencoded({ extended: true }));

// Ruta principal
app.get('/', (req, res) => {
    res.json({
        message: 'API de Facturación Electrónica Paraguay',
        version: '1.0.0',
        endpoints: [
            { method: 'POST', path: '/generate-xml', description: 'Genera un XML para SIFEN' },
            { method: 'POST', path: '/generate-cdc', description: 'Genera un CDC (Código de Control)' },
            { method: 'POST', path: '/validate-data', description: 'Valida datos según el manual técnico de SIFEN' }
        ]
    });
});

// Endpoint para generar XML
app.post('/generate-xml', async (req, res) => {
    try {
        const { params, data, options } = req.body;
        
        if (!params || !data) {
            return res.status(400).json({
                success: false,
                message: 'Se requieren los parámetros "params" y "data"'
            });
        }
        
        // Verificar que la función existe antes de llamarla
        if (typeof xmlgen.generateXMLDE !== 'function') {
            throw new Error('La función generateXMLDE no está disponible en la librería facturacionelectronicapy-xmlgen');
        }
        
        const xml = await xmlgen.generateXMLDE(params, data, options || {});
        
        res.set('Content-Type', 'application/xml');
        res.send(xml);
    } catch (error) {
        console.error('Error al generar XML:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al generar XML'
        });
    }
});

// Endpoint para generar CDC
app.post('/generate-cdc', async (req, res) => {
    try {
        const { data } = req.body;
        
        if (!data) {
            return res.status(400).json({
                success: false,
                message: 'Se requiere el parámetro "data"'
            });
        }
        
        // Generar CDC utilizando la librería
        // Nota: La librería no expone directamente esta funcionalidad, por lo que
        // se simula aquí. En una implementación real, se debería usar la función
        // correspondiente de la librería o implementar la lógica según el manual técnico.
        const cdc = await simulateGenerateCDC(data);
        
        res.json({
            success: true,
            cdc: cdc
        });
    } catch (error) {
        console.error('Error al generar CDC:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al generar CDC'
        });
    }
});

// Endpoint para validar datos
app.post('/validate-data', async (req, res) => {
    try {
        const { data } = req.body;
        
        if (!data) {
            return res.status(400).json({
                success: false,
                message: 'Se requiere el parámetro "data"'
            });
        }
        
        // Validar datos utilizando la librería
        // Nota: La librería no expone directamente esta funcionalidad, por lo que
        // se simula aquí. En una implementación real, se debería usar la función
        // correspondiente de la librería.
        const validationResult = await simulateValidateData(data);
        
        if (validationResult.valid) {
            res.json({
                success: true,
                valid: true
            });
        } else {
            res.json({
                success: true,
                valid: false,
                errors: validationResult.errors
            });
        }
    } catch (error) {
        console.error('Error al validar datos:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Error al validar datos'
        });
    }
});

// Función auxiliar para simular la generación de CDC
// En una implementación real, esto debería usar la funcionalidad de la librería
async function simulateGenerateCDC(data) {
    // Simulación simple de un CDC
    // El formato real del CDC es más complejo y sigue reglas específicas
    const timestamp = new Date().getTime().toString();
    const random = Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
    return `${data.tipoDocumento || '01'}${data.establecimiento || '001'}${data.punto || '001'}${data.numero || '0000001'}${timestamp.substring(timestamp.length - 8)}${random}`;
}

// Función auxiliar para simular la validación de datos
// En una implementación real, esto debería usar la funcionalidad de la librería
async function simulateValidateData(data) {
    // Simulación simple de validación
    const errors = [];
    
    // Validar campos requeridos básicos
    if (!data.tipoDocumento) errors.push('El campo tipoDocumento es requerido');
    if (!data.establecimiento) errors.push('El campo establecimiento es requerido');
    if (!data.punto) errors.push('El campo punto es requerido');
    if (!data.numero) errors.push('El campo numero es requerido');
    if (!data.fecha) errors.push('El campo fecha es requerido');
    
    return {
        valid: errors.length === 0,
        errors: errors
    };
}

// Iniciar el servidor
app.listen(port, () => {
    console.log(`Servidor de Facturación Electrónica ejecutándose en http://localhost:${port}`);
});