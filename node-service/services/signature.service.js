/**
 * Servicio para firmar documentos XML con certificado digital
 */

const fs = require('fs');
const xmlCrypto = require('xml-crypto');
const { SignedXml } = xmlCrypto;
const { DOMParser } = require('xmldom');

/**
 * Firma un documento XML utilizando un certificado digital
 * 
 * @param {string} xml - Contenido del XML a firmar
 * @param {string} certPath - Ruta al archivo del certificado digital (PKCS#12)
 * @param {string} certPassword - Contraseña del certificado
 * @param {object} options - Opciones adicionales para la firma
 * @returns {Promise<string>} - XML firmado
 */
async function signXML(xml, certPath, certPassword, options = {}) {
    try {
        console.log(`Firmando XML con certificado: ${certPath}`);
        
        // Verificar que el certificado exista
        if (!fs.existsSync(certPath)) {
            throw new Error(`El archivo de certificado no existe: ${certPath}`);
        }
        
        // Leer el certificado
        const pfx = fs.readFileSync(certPath);
        console.log(`Certificado leído. Tamaño: ${pfx.length} bytes`);
        
        // Crear el objeto de firma
        const sig = new SignedXml();
        sig.signingKey = {
            key: pfx,
            passphrase: certPassword,
            format: 'pkcs12'
        };
        
        // Configurar dónde se insertará la firma
        const signatureLocation = options.signatureLocation || '/*';
        
        // Configurar la firma enveloped
        sig.addReference(
            "",
            [
                "http://www.w3.org/2000/09/xmldsig#enveloped-signature",
                "http://www.w3.org/TR/2001/REC-xml-c14n-20010315"
            ],
            "http://www.w3.org/2001/04/xmlenc#sha256"
        );
        
        // Configurar algoritmos
        sig.signatureAlgorithm = "http://www.w3.org/2001/04/xmldsig-more#rsa-sha256";
        sig.canonicalizationAlgorithm = "http://www.w3.org/TR/2001/REC-xml-c14n-20010315";
        
        // Verificar si el XML ya tiene etiqueta de firma
        if (xml.includes('<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">')) {
            console.log('El XML ya contiene una etiqueta de firma. Se reemplazará la firma existente.');
            
            // Eliminar la firma existente antes de firmar
            xml = xml.replace(/<Signature xmlns="http:\/\/www.w3.org\/2000\/09\/xmldsig#">[\s\S]*?<\/Signature>/, '');
        }
        
        // Firmar el XML
        sig.computeSignature(xml);
        const signedXml = sig.getSignedXml();
        
        console.log('XML firmado correctamente');
        
        return signedXml;
    } catch (error) {
        console.error('Error al firmar XML:', error);
        throw error;
    }
}

/**
 * Verifica la firma de un documento XML
 * 
 * @param {string} signedXml - XML firmado a verificar
 * @returns {Promise<object>} - Resultado de la verificación
 */
async function verifyXMLSignature(signedXml) {
    try {
        console.log('Verificando firma XML...');
        
        const doc = new DOMParser().parseFromString(signedXml);
        const signature = xmlCrypto.xpath(doc, "//*//*[local-name(.)='Signature' and namespace-uri(.)='http://www.w3.org/2000/09/xmldsig#']")[0];
        
        if (!signature) {
            return {
                valid: false,
                error: 'No se encontró una firma en el documento XML'
            };
        }
        
        const sig = new xmlCrypto.SignedXml();
        sig.loadSignature(signature);
        const verified = sig.checkSignature(signedXml);
        
        if (!verified) {
            return {
                valid: false,
                error: 'La firma no es válida',
                detail: sig.validationErrors.join(', ')
            };
        }
        
        return {
            valid: true,
            message: 'Firma verificada correctamente'
        };
    } catch (error) {
        console.error('Error al verificar firma XML:', error);
        return {
            valid: false,
            error: error.message
        };
    }
}

// Exportar las funciones
module.exports = {
    signXML,
    verifyXMLSignature
};
