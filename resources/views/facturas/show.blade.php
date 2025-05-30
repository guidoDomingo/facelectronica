@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detalle de Factura Electrónica</h4>
                    <div>
                        <a href="{{ route('facturas.descargar-xml', $factura) }}" class="btn btn-sm btn-light me-2">
                            <i class="bi bi-file-earmark-code"></i> Descargar XML
                        </a>
                        <a href="{{ route('facturas.index') }}" class="btn btn-sm btn-light">
                            <i class="bi bi-arrow-left"></i> Volver
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Información del Documento</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">CDC:</th>
                                            <td>{{ $factura->cdc }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tipo de Documento:</th>
                                            <td>
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
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Número:</th>
                                            <td>{{ $factura->establecimiento }}-{{ $factura->punto }}-{{ $factura->numero }}</td>
                                        </tr>
                                        <tr>
                                            <th>Fecha de Emisión:</th>
                                            <td>{{ $factura->fecha->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Estado:</th>
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
                                        </tr>
                                        <tr>
                                            <th>Moneda:</th>
                                            <td>{{ $factura->moneda }}</td>
                                        </tr>
                                        @if($factura->observacion)
                                            <tr>
                                                <th>Observación:</th>
                                                <td>{{ $factura->observacion }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">Emisor</h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-1"><strong>{{ $factura->razon_social_emisor }}</strong></p>
                                            <p class="mb-1">RUC: {{ $factura->ruc_emisor }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">Receptor</h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-1"><strong>{{ $factura->razon_social_receptor }}</strong></p>
                                            <p class="mb-1">RUC: {{ $factura->ruc_receptor }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aquí iría el detalle de ítems cuando implementes la tabla de ítems -->
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Detalles del documento</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                Los detalles de los ítems se mostrarán aquí una vez que implemente la tabla de detalles de factura.
                            </div>

                            <div class="row mt-3 justify-content-end">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <span>Subtotal:</span>
                                                <span>{{ number_format($factura->total - $factura->impuesto, 0, ',', '.') }} {{ $factura->moneda }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>IVA:</span>
                                                <span>{{ number_format($factura->impuesto, 0, ',', '.') }} {{ $factura->moneda }}</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between fw-bold">
                                                <span>Total:</span>
                                                <span>{{ number_format($factura->total, 0, ',', '.') }} {{ $factura->moneda }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Eventos de la factura -->
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Historial de Eventos</h5>
                        </div>
                        <div class="card-body">
                            @if($factura->eventos->isEmpty())
                                <div class="alert alert-info">
                                    No hay eventos registrados para esta factura.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Tipo</th>
                                                <th>Descripción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($factura->eventos as $evento)
                                                <tr>
                                                    <td>{{ $evento->created_at->format('d/m/Y H:i:s') }}</td>
                                                    <td>{{ $evento->tipo }}</td>
                                                    <td>{{ $evento->descripcion }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>                    <!-- QR Code -->
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Código QR</h5>
                        </div>
                        <div class="card-body text-center">                            <div class="border p-3 mb-3 rounded shadow-sm d-inline-block">
                                <img id="qr-image" src="{{ route('facturas.qr', $factura) }}" alt="Código QR SIFEN" class="img-fluid" style="max-width: 200px;">
                            </div>
                            <div class="mt-2">
                                <p class="small text-muted">QR generado según especificaciones de SIFEN</p>
                                
                                @if($factura->xml)
                                    <div class="alert alert-success py-2">
                                        <i class="bi bi-check-circle"></i> XML disponible para la generación del QR
                                    </div>
                                @else
                                    <div class="alert alert-warning py-2">
                                        <i class="bi bi-exclamation-triangle"></i> Advertencia: No hay XML disponible, el QR podría no ser válido
                                    </div>
                                @endif
                            </div>
                              <div class="mt-3">
                                <p class="mb-1 small fw-bold">Información incluida en el QR:</p>
                                <ul class="list-unstyled small text-start mx-auto" style="max-width: 300px;">
                                    <li><span class="fw-bold">Versión:</span> 150</li>
                                    <li><span class="fw-bold">CDC:</span> {{ $factura->cdc }}</li>
                                    <li><span class="fw-bold">Fecha:</span> {{ $factura->fecha->format('Y-m-d') }}</li>
                                    <li><span class="fw-bold">RUC Receptor:</span> {{ $factura->ruc_receptor }}</li>
                                    <li><span class="fw-bold">Total:</span> {{ number_format($factura->total, 0, '', '') }} {{ $factura->moneda }}</li>
                                    <li><span class="fw-bold">IVA:</span> {{ number_format($factura->impuesto, 0, '', '') }} {{ $factura->moneda }}</li>
                                    <li><span class="fw-bold">Items:</span> 
                                        @if($factura->xml && strpos($factura->xml, '<gCamItem>') !== false)
                                            {{ substr_count($factura->xml, '<gCamItem>') }}
                                        @else
                                            1
                                        @endif
                                    </li>
                                    <li><span class="fw-bold">CSC ID:</span> {{ config('facturacion_electronica.id_csc', '0001') }}</li>
                                </ul>
                                
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshQR()">
                                        <i class="bi bi-arrow-clockwise"></i> Regenerar QR
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="downloadQR()">
                                        <i class="bi bi-download"></i> Descargar QR
                                    </button>
                                </div>
                            </div>
                            
                            <script>
                            function refreshQR() {
                                const img = document.querySelector('#qr-image');
                                const timestamp = new Date().getTime();
                                img.src = img.src.split('?')[0] + '?refresh=' + timestamp;
                            }
                            
                            function downloadQR() {
                                const link = document.createElement('a');
                                link.href = '{{ route('facturas.qr', $factura) }}';
                                link.download = 'qr_{{ $factura->cdc }}.png';
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            }
                            </script>
                        </div>
                    </div>                    <!-- Acciones -->
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Acciones de SIFEN</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-grid gap-2 mb-3">
                                        <a href="{{ route('facturas.verificar-sifen', $factura) }}" class="btn btn-info">
                                            <i class="bi bi-search"></i> Verificar Estado en SIFEN
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-grid gap-2">
                                        <form action="{{ route('facturas.enviar-sifen', $factura) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100" {{ $factura->xml ? '' : 'disabled' }}>
                                                <i class="bi bi-send"></i> Enviar a SIFEN
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3 mb-0">
                                <p class="mb-0"><i class="bi bi-info-circle"></i> <strong>Nota:</strong> En un entorno de producción, estas acciones interactúan directamente con el servicio web de SIFEN. Actualmente, están en modo de simulación.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('facturas.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        
                        @if($factura->estado == 'aceptada')
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#cancelarModal">
                                <i class="bi bi-x-circle"></i> Cancelar Factura
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cancelar factura -->
@if($factura->estado == 'aceptada')
    <div class="modal fade" id="cancelarModal" tabindex="-1" aria-labelledby="cancelarModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('facturas.cancelar', $factura) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelarModalLabel">Cancelar Factura</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro que desea cancelar esta factura?</p>
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
@endsection
