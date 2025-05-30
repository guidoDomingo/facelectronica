@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Facturas Electrónicas</h4>
                    <a href="{{ route('facturas.create') }}" class="btn btn-sm btn-light">
                        <i class="bi bi-plus"></i> Nueva Factura
                    </a>
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
                    
                    @if($facturas->isEmpty())
                        <div class="alert alert-info">
                            No hay facturas electrónicas registradas.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Número</th>
                                        <th>Fecha</th>
                                        <th>RUC Receptor</th>
                                        <th>Razón Social</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($facturas as $factura)
                                        <tr>
                                            <td>{{ $factura->tipo_documento }}</td>
                                            <td>{{ $factura->establecimiento }}-{{ $factura->punto }}-{{ $factura->numero }}</td>
                                            <td>{{ $factura->fecha->format('d/m/Y H:i') }}</td>
                                            <td>{{ $factura->ruc_receptor }}</td>
                                            <td>{{ $factura->razon_social_receptor }}</td>
                                            <td>{{ number_format($factura->total, 0, ',', '.') }} {{ $factura->moneda }}</td>
                                            <td>
                                                <span class="badge rounded-pill 
                                                    @if($factura->estado == 'aceptada') bg-success 
                                                    @elseif($factura->estado == 'rechazada') bg-danger
                                                    @elseif($factura->estado == 'enviada') bg-primary
                                                    @elseif($factura->estado == 'cancelada') bg-warning text-dark
                                                    @elseif($factura->estado == 'inutilizada') bg-secondary
                                                    @else bg-info text-dark @endif">
                                                    {{ ucfirst($factura->estado) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group" aria-label="Acciones">
                                                    <a href="{{ route('facturas.show', $factura) }}" class="btn btn-sm btn-info" title="Ver detalle">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('facturas.descargar-xml', $factura) }}" class="btn btn-sm btn-secondary" title="Descargar XML">
                                                        <i class="bi bi-file-earmark-code"></i>
                                                    </a>
                                                    
                                                    @if($factura->estado == 'aceptada')
                                                        <button type="button" class="btn btn-sm btn-warning" title="Cancelar" 
                                                                data-bs-toggle="modal" data-bs-target="#cancelarModal-{{ $factura->id }}">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-4">
                            {{ $facturas->links() }}
                        </div>
                        
                        <!-- Modales de cancelación -->
                        @foreach($facturas as $factura)
                            @if($factura->estado == 'aceptada')
                                <div class="modal fade" id="cancelarModal-{{ $factura->id }}" tabindex="-1" aria-labelledby="cancelarModalLabel-{{ $factura->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('facturas.cancelar', $factura) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="cancelarModalLabel-{{ $factura->id }}">Cancelar Factura</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>¿Está seguro que desea cancelar la factura {{ $factura->establecimiento }}-{{ $factura->punto }}-{{ $factura->numero }}?</p>
                                                    <div class="form-group">
                                                        <label for="motivo">Motivo de cancelación:</label>
                                                        <textarea name="motivo" id="motivo" rows="3" class="form-control" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    <button type="submit" class="btn btn-primary">Confirmar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
