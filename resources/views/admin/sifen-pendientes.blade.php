@extends('layouts.app')

@section('title', 'Documentos Pendientes SIFEN')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Documentos Pendientes - SIFEN</h5>
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
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Pendientes</h5>
                    <h2>{{ $facturasPendientes->total() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Pendientes Hoy</h5>
                    <h2>{{ $estadisticas['pendientesHoy'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Pendientes Última Semana</h5>
                    <h2>{{ $estadisticas['pendientesSemana'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Acciones</h5>
                    <a href="#" class="btn btn-sm btn-light" onclick="verificarTodos()">Verificar Todos</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Documentos Pendientes</h5>
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
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Observación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($facturasPendientes as $factura)
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
                                        <td>{{ number_format($factura->total, 0, ',', '.') }} {{ $factura->moneda }}</td>
                                        <td>
                                            <span class="badge {{ $factura->estado === 'enviada' ? 'bg-warning' : 'bg-secondary' }}">
                                                {{ ucfirst($factura->estado) }}
                                            </span>
                                        </td>
                                        <td>{{ $factura->observacion ?? 'Sin observaciones' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('facturas.show', $factura) }}" class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form action="{{ route('admin.sifen.verificar', $factura) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary" title="Verificar Estado">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                </form>
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
                                        <td colspan="8" class="text-center">No hay documentos pendientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $facturasPendientes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function verificarTodos() {
        if (!confirm('¿Está seguro de verificar el estado de todos los documentos pendientes? Esto puede llevar tiempo.')) {
            return;
        }
        
        alert('Esta acción será implementada próximamente');
    }
</script>
@endpush
