@extends('layouts.app')

@section('title', 'Errores SIFEN')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Errores y Rechazos - SIFEN</h5>
                    <div>
                        <a href="{{ route('admin.sifen.dashboard') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Documentos Rechazados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>CDC</th>
                                    <th>Tipo/Número</th>
                                    <th>Fecha</th>
                                    <th>Receptor</th>
                                    <th>Observación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($facturasRechazadas as $factura)
                                    <tr>
                                        <td>
                                            {{ Str::limit($factura->cdc, 10) }}...
                                            <button class="btn btn-sm btn-link p-0" 
                                                onclick="navigator.clipboard.writeText('{{ $factura->cdc }}')">
                                                <i class="fas fa-copy" title="Copiar CDC"></i>
                                            </button>
                                        </td>
                                        <td>
                                            {{ $factura->tipo_documento }} {{ $factura->establecimiento }}-{{ $factura->punto }}-{{ str_pad($factura->numero, 7, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td>{{ $factura->fecha->format('d/m/Y H:i') }}</td>
                                        <td>{{ $factura->razon_social_receptor }} ({{ $factura->ruc_receptor }})</td>
                                        <td>{{ $factura->observacion ?? 'Sin información' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('facturas.show', $factura) }}" class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form action="{{ route('admin.sifen.reintentar', $factura) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning" title="Reintentar Envío">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay documentos rechazados</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $facturasRechazadas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Eventos de Error</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Factura</th>
                                    <th>Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($eventosError as $evento)
                                    <tr>
                                        <td>{{ $evento->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td>
                                            <span class="badge bg-danger">{{ $evento->tipo }}</span>
                                        </td>
                                        <td>{{ $evento->descripcion }}</td>
                                        <td>
                                            @if($evento->facturaElectronica)
                                                <a href="{{ route('facturas.show', $evento->facturaElectronica) }}">
                                                    {{ Str::limit($evento->facturaElectronica->cdc ?? 'Sin CDC', 10) }}...
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#eventoModal{{ $evento->id }}">
                                                <i class="fas fa-search-plus"></i> Ver detalles
                                            </button>
                                            
                                            <!-- Modal para detalles -->
                                            <div class="modal fade" id="eventoModal{{ $evento->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detalles del Error</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <h6>Información del Error</h6>
                                                                <p><strong>Tipo:</strong> {{ $evento->tipo }}</p>
                                                                <p><strong>Descripción:</strong> {{ $evento->descripcion }}</p>
                                                                <p><strong>Fecha:</strong> {{ $evento->created_at->format('d/m/Y H:i:s') }}</p>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <h6>Datos Adicionales</h6>
                                                                <pre class="bg-light p-3">{{ json_encode($evento->datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No hay eventos de error registrados</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $eventosError->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
