@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Facturación Electrónica Paraguay</h3>
                </div>
                <div class="card-body">"<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación Electrónica Paraguay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Facturación Electrónica Paraguay</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <p>Esta es una implementación de ejemplo para la integración con el servicio de facturación electrónica de Paraguay (SIFEN).</p>
                            <p>Utiliza el módulo <code>facturacionelectronicapy-xmlgen</code> a través de un servicio Node.js.</p>
                        </div>
                        
                        <h4 class="mt-4">Ejemplos</h4>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Generar XML</h5>
                                        <p class="card-text">Genera un documento XML de ejemplo para SIFEN.</p>
                                        <a href="{{ route('facturacion.generar-ejemplo') }}" class="btn btn-primary">Generar XML</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Generar CDC</h5>
                                        <p class="card-text">Genera un CDC (Código de Control) de ejemplo.</p>
                                        <button id="generarCDC" class="btn btn-primary">Generar CDC</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="resultado" class="mt-4" style="display: none;">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    Resultado
                                </div>
                                <div class="card-body">
                                    <pre id="resultadoContenido" class="bg-light p-3 rounded"></pre>                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.getElementById('generarCDC').addEventListener('click', function() {
        fetch('{{ route("facturacion.generar-cdc-ejemplo") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('resultadoContenido').textContent = JSON.stringify(data, null, 2);
            document.getElementById('resultado').style.display = 'block';
        })
        .catch(error => {
            document.getElementById('resultadoContenido').textContent = 'Error: ' + error.message;
            document.getElementById('resultado').style.display = 'block';
        });
    });
</script>
@endsection