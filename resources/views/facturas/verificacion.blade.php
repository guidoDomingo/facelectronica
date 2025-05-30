@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Verificación en SIFEN</h4>
                    <div>
                        <a href="{{ route('facturas.show', $factura) }}" class="btn btn-sm btn-light">
                            <i class="bi bi-arrow-left"></i> Volver a Factura
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Información del Documento</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>CDC:</strong> {{ $factura->cdc }}</p>
                                    <p><strong>Tipo:</strong> 
                                        @switch($factura->tipo_documento)
                                            @case(1)
                                                Factura Electrónica
                                                @break
                                            @case(4)
                                                Autofactura Electrónica
                                                @break
                                            @case(5)
                                                Nota de Crédito Electrónica
                                                @break
                                            @case(6)
                                                Nota de Débito Electrónica
                                                @break
                                            @case(7)
                                                Nota de Remisión Electrónica
                                                @break
                                            @default
                                                Desconocido
                                        @endswitch
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Número:</strong> {{ $factura->establecimiento }}-{{ $factura->punto }}-{{ $factura->numero }}</p>
                                    <p><strong>Fecha:</strong> {{ $factura->fecha->format('d/m/Y H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Resultado de la Verificación</h5>
                        </div>
                        <div class="card-body">
                            @if($resultado['estado'] == 'simulado')
                                <div class="alert alert-warning mb-3">
                                    <h6><i class="bi bi-exclamation-triangle me-2"></i> Modo de Simulación</h6>
                                    <p class="mb-0">Esta es una simulación y no representa una consulta real a SIFEN. En un entorno de producción, mostraría la respuesta del servicio web de SIFEN.</p>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="30%" class="bg-light">Estado</th>
                                            <td>{{ $resultado['respuesta']['estado'] ?? 'No disponible' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Código</th>
                                            <td>{{ $resultado['respuesta']['codigo'] ?? 'No disponible' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Mensaje</th>
                                            <td>{{ $resultado['respuesta']['mensaje'] ?? 'No disponible' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Fecha Proceso</th>
                                            <td>{{ $resultado['respuesta']['fechaProceso'] ?? 'No disponible' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3">
                                <div class="alert alert-info">
                                    <h6 class="mb-2">Interpretación del resultado:</h6>
                                    <p class="mb-0">
                                        @if(isset($resultado['respuesta']['codigo']))
                                            @if($resultado['respuesta']['codigo'] == '0')
                                                El documento ha sido procesado correctamente en SIFEN.
                                            @elseif($resultado['respuesta']['codigo'] == '100')
                                                El documento ha sido rechazado por SIFEN.
                                            @else
                                                Consulte la documentación oficial de SIFEN para más detalles sobre este código de respuesta.
                                            @endif
                                        @else
                                            No se pudo determinar el resultado de la consulta.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Acciones Disponibles</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('facturas.verificar-sifen', $factura) }}" class="btn btn-primary">
                                            <i class="bi bi-arrow-clockwise"></i> Verificar Nuevamente
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-grid gap-2">
                                        <form action="{{ route('facturas.enviar-sifen', $factura) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100">
                                                <i class="bi bi-send"></i> Enviar a SIFEN
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('facturas.show', $factura) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver a Factura
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
