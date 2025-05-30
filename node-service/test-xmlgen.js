/**
 * Script de prueba para verificar cómo se importa y usa correctamente la biblioteca facturacionelectronicapy-xmlgen
 */

console.log('Iniciando prueba de la biblioteca facturacionelectronicapy-xmlgen...');

// Intentar diferentes formas de importar
try {
    // Intento 1: Importar directamente
    const xmlgen1 = require('facturacionelectronicapy-xmlgen');
    console.log('Importación directa, tipo:', typeof xmlgen1);
    console.log('Es objeto:', typeof xmlgen1 === 'object');
    console.log('Tiene default:', xmlgen1.default !== undefined);
    console.log('Métodos disponibles:', Object.keys(xmlgen1));

    // Intento 2: Importar con .default si está disponible
    const xmlgen2 = xmlgen1.default || xmlgen1;
    console.log('\nImportación con .default, tipo:', typeof xmlgen2);
    console.log('Métodos disponibles:', Object.keys(xmlgen2));

    // Intento 3: Crear una nueva instancia si es una clase
    let xmlgen3;
    if (typeof xmlgen2 === 'function') {
        xmlgen3 = new xmlgen2();
        console.log('\nNueva instancia, tipo:', typeof xmlgen3);
        console.log('Métodos disponibles:', Object.keys(xmlgen3));
    } else {
        console.log('\nNo es una clase, no se puede crear instancia');
    }
} catch (error) {
    console.error('Error al importar la biblioteca:', error);
}
