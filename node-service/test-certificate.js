const fs = require('fs');
const path = require('path');
const xmlCrypto = require('xml-crypto');
const { SignedXml } = xmlCrypto;

/**
 * Script de prueba para firma de XML con certificado digital
 */
async function testCertificate(certPath, certPassword) {
    console.log(`Probando certificado en: ${certPath}`);
    
    try {
        // Verificar que el archivo exista
        if (!fs.existsSync(certPath)) {
            console.error(`El archivo de certificado no existe: ${certPath}`);
            return false;
        }
        
        // Leer el certificado
        const pfx = fs.readFileSync(certPath);
        console.log(`Certificado leído. Tamaño: ${pfx.length} bytes`);
        
        // Crear un XML de prueba
        const xmlTemplate = `<?xml version="1.0" encoding="UTF-8"?>
<DE xmlns="http://ekuatia.set.gov.py/sifen/xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <dDVId>1</dDVId>
    <dFecFirma>2022-05-30T08:00:00</dFecFirma>
    <dDatGralOpe>
        <dFecEmi>2022-05-30</dFecEmi>
    </dDatGralOpe>
    <Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
        <SignedInfo>
            <CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
            <SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
            <Reference URI="">
                <Transforms>
                    <Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
                </Transforms>
                <DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
                <DigestValue></DigestValue>
            </Reference>
        </SignedInfo>
        <SignatureValue></SignatureValue>
        <KeyInfo>
            <X509Data>
                <X509Certificate></X509Certificate>
            </X509Data>
        </KeyInfo>
    </Signature>
</DE>`;

        console.log('XML de prueba creado');
        console.log('Intentando firmar XML...');
        
        // Configurar la firma
        const sig = new SignedXml();
        sig.signingKey = {
            key: pfx,
            passphrase: certPassword,
            format: 'pkcs12'
        };
        
        // Configurar la firma enveloped
        sig.addReference(
            "",
            [
                "http://www.w3.org/2000/09/xmldsig#enveloped-signature",
                "http://www.w3.org/TR/2001/REC-xml-c14n-20010315"
            ],
            "http://www.w3.org/2001/04/xmlenc#sha256"
        );
        
        sig.signatureAlgorithm = "http://www.w3.org/2001/04/xmldsig-more#rsa-sha256";
        sig.canonicalizationAlgorithm = "http://www.w3.org/TR/2001/REC-xml-c14n-20010315";
        
        // Firmar el XML
        sig.computeSignature(xmlTemplate);
        const signedXml = sig.getSignedXml();
        
        console.log('XML firmado correctamente');
        console.log('Tamaño del XML firmado:', signedXml.length);
        
        // Guardar XML firmado como referencia
        const outputPath = path.join(__dirname, 'test-signed.xml');
        fs.writeFileSync(outputPath, signedXml);
        console.log(`XML firmado guardado en: ${outputPath}`);
        
        return true;
    } catch (error) {
        console.error('Error al probar el certificado:', error);
        return false;
    }
}

// Verificar si hay argumentos en la línea de comandos
if (process.argv.length < 4) {
    console.log('Uso: node test-certificate.js <ruta-certificado> <contraseña>');
    console.log('Ejemplo: node test-certificate.js ./certificados/cert.p12 micontraseña');
    process.exit(1);
}

// Obtener parámetros
const certPath = process.argv[2];
const certPassword = process.argv[3];

// Ejecutar la prueba
testCertificate(certPath, certPassword)
    .then(result => {
        if (result) {
            console.log('✅ Certificado verificado con éxito');
            process.exit(0);
        } else {
            console.error('❌ Error al verificar el certificado');
            process.exit(1);
        }
    })
    .catch(error => {
        console.error('Error inesperado:', error);
        process.exit(1);
    });
