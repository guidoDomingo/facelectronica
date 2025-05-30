<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración para Facturación Electrónica Paraguay (SIFEN)
    |--------------------------------------------------------------------------
    |
    | Esta configuración es necesaria para la integración con el servicio
    | de facturación electrónica de Paraguay (SIFEN) a través del módulo
    | facturacionelectronicapy-xmlgen.
    |
    */    // URL del servicio Node.js que ejecuta facturacionelectronicapy-xmlgen
    'node_api_url' => env('FACTURACION_ELECTRONICA_NODE_API_URL', 'http://localhost:3000'),
    
    /*
    |--------------------------------------------------------------------------
    | Configuración para la firma digital
    |--------------------------------------------------------------------------
    |
    | Configuración para la firma digital de los documentos XML
    |
    */
    'firma_digital' => [
        'ruta_certificado' => env('FACTURACION_CERT_PATH', storage_path('app/certificados/certificado.p12')),
        'clave_certificado' => env('FACTURACION_CERT_CLAVE', 'clave_certificado'),
        'habilitada' => env('FACTURACION_FIRMA_HABILITADA', false),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configuración para el Código de Seguridad y Control (CSC)
    |--------------------------------------------------------------------------
    |
    | Configuración para el CSC proporcionado por SIFEN
    |
    */
    'id_csc' => env('FACTURACION_ID_CSC', '0001'),
    'codigo_csc' => env('FACTURACION_CODIGO_CSC', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Ambiente SIFEN
    |--------------------------------------------------------------------------
    |
    | Configuración del ambiente de SIFEN
    | - test: ambiente de pruebas
    | - prod: ambiente de producción
    |
    */
    'ambiente' => env('FACTURACION_AMBIENTE', 'test'),
    
    /*
    |--------------------------------------------------------------------------
    | URLs de los servicios web de SIFEN
    |--------------------------------------------------------------------------
    |
    | URLs de los servicios web de SIFEN para test y producción
    |
    */
    'urls' => [
        'test' => [
            'consulta' => env('FACTURACION_URL_CONSULTA_TEST', 'https://sifen-test.set.gov.py/de/ws/consultas-services.wsdl'),
            'recepcion' => env('FACTURACION_URL_RECEPCION_TEST', 'https://sifen-test.set.gov.py/de/ws/sync-services.wsdl'),
            'eventos' => env('FACTURACION_URL_EVENTOS_TEST', 'https://sifen-test.set.gov.py/de/ws/eventos-services.wsdl'),
        ],
        'prod' => [
            'consulta' => env('FACTURACION_URL_CONSULTA_PROD', 'https://sifen.set.gov.py/de/ws/consultas-services.wsdl'),
            'recepcion' => env('FACTURACION_URL_RECEPCION_PROD', 'https://sifen.set.gov.py/de/ws/sync-services.wsdl'),
            'eventos' => env('FACTURACION_URL_EVENTOS_PROD', 'https://sifen.set.gov.py/de/ws/eventos-services.wsdl'),
        ],
    ],
    
    // Datos del contribuyente por defecto
    'contribuyente' => [
        'version' => 150,
        'ruc' => env('FACTURACION_ELECTRONICA_RUC', ''),
        'razonSocial' => env('FACTURACION_ELECTRONICA_RAZON_SOCIAL', ''),
        'nombreFantasia' => env('FACTURACION_ELECTRONICA_NOMBRE_FANTASIA', ''),
        'timbradoNumero' => env('FACTURACION_ELECTRONICA_TIMBRADO_NUMERO', ''),
        'timbradoFecha' => env('FACTURACION_ELECTRONICA_TIMBRADO_FECHA', ''),
        'tipoContribuyente' => env('FACTURACION_ELECTRONICA_TIPO_CONTRIBUYENTE', 1),
        'tipoRegimen' => env('FACTURACION_ELECTRONICA_TIPO_REGIMEN', 8),
    ],
      // Actividades económicas del contribuyente
    'actividades_economicas' => [
        ['codigo' => '62010', 'descripcion' => 'Desarrollo de Software y Sistemas Informáticos']
    ],
      // Establecimientos del contribuyente
    'establecimientos' => [
        [
            'codigo' => '001',
            'direccion' => 'Barrio Carolina',
            'numeroCasa' => '0',
            'complementoDireccion1' => 'Entre calle 2',
            'complementoDireccion2' => 'y Calle 7',
            'departamento' => 11,
            'departamentoDescripcion' => 'ALTO PARANA',
            'distrito' => 145,
            'distritoDescripcion' => 'CIUDAD DEL ESTE',
            'ciudad' => 3432,
            'ciudadDescripcion' => 'CIUDAD DEL ESTE',
            'telefono' => '0000-000000',
            'email' => 'contacto@empresa.com',
            'denominacion' => 'Casa Matriz',
        ]
    ],
];