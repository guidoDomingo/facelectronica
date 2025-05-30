@extends('layouts.app')

@section('title', 'Dashboard SIFEN')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Dashboard de Facturación Electrónica - SIFEN</h5>
                    <div>
                        <span class="badge {{ $ambiente == 'prod' ? 'bg-success' : 'bg-warning' }}">
                            Ambiente: {{ $ambiente == 'prod' ? 'PRODUCCIÓN' : 'PRUEBAS' }}
                        </span>
                        <span class="badge {{ $modoDesarrollo ? 'bg-info' : 'bg-secondary' }}">
                            Modo: {{ $modoDesarrollo ? 'DESARROLLO' : 'PRODUCCIÓN' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Estadísticas -->
    <div class="row">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Documentos</h5>
                    <h2>{{ $estadisticas['total'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Aceptados</h5>
                    <h2>{{ $estadisticas['aceptadas'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Pendientes</h5>
                    <h2>{{ $estadisticas['pendientes'] }}</h2>
                    <a href="{{ route('admin.sifen.pendientes') }}" class="text-white">Ver detalles</a>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Rechazados</h5>
                    <h2>{{ $estadisticas['rechazadas'] }}</h2>
                    <a href="{{ route('admin.sifen.errores') }}" class="text-white">Ver detalles</a>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Cancelados</h5>
                    <h2>{{ $estadisticas['canceladas'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Inutilizados</h5>
                    <h2>{{ $estadisticas['inutilizadas'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado del Certificado -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Estado del Certificado Digital</h5>
                </div>
                <div class="card-body">
                    @if ($certificadoInfo)
                        <table class="table">
                            <tr>
                                <th>Sujeto:</th>
                                <td>{{ $certificadoInfo['subject'] }}</td>
                            </tr>
                            <tr>
                                <th>Emisor:</th>
                                <td>{{ $certificadoInfo['issuer'] }}</td>
                            </tr>
                            <tr>
                                <th>Válido desde:</th>
                                <td>{{ $certificadoInfo['valid_from'] }}</td>
                            </tr>
                            <tr>
                                <th>Válido hasta:</th>
                                <td>
                                    {{ $certificadoInfo['valid_to'] }}
                                    @if($certificadoInfo['days_remaining'] <= 30)
                                        <span class="badge bg-danger">
                                            Expira en {{ $certificadoInfo['days_remaining'] }} días
                                        </span>
                                    @elseif($certificadoInfo['days_remaining'] <= 90)
                                        <span class="badge bg-warning">
                                            Expira en {{ $certificadoInfo['days_remaining'] }} días
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            {{ $certificadoInfo['days_remaining'] }} días restantes
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                        <a href="{{ route('admin.sifen.certificado') }}" class="btn btn-sm btn-primary">
                            Gestionar Certificado
                        </a>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No se encontró un certificado digital válido configurado.
                            <a href="{{ route('admin.sifen.certificado') }}" class="alert-link">
                                Configurar certificado
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Facturas Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>CDC</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($facturasRecientes as $factura)
                                    <tr>
                                        <td>{{ Str::limit($factura->cdc, 12) }}...</td>
                                        <td>{{ $factura->fecha->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge {{ $factura->estado === 'aceptada' ? 'bg-success' : 
                                                ($factura->estado === 'rechazada' ? 'bg-danger' : 
                                                ($factura->estado === 'generada' ? 'bg-secondary' : 'bg-warning')) }}">
                                                {{ ucfirst($factura->estado) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('facturas.show', $factura) }}" class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form action="{{ route('admin.sifen.verificar', $factura) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary" title="Verificar Estado">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No hay facturas recientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Eventos Recientes -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Eventos SIFEN Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Factura</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($eventosRecientes as $evento)
                                    <tr>
                                        <td>{{ $evento->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="badge {{ str_contains($evento->tipo, 'error') ? 'bg-danger' : 
                                                (str_contains($evento->tipo, 'consulta') ? 'bg-info' : 'bg-primary') }}">
                                                {{ $evento->tipo }}
                                            </span>
                                        </td>
                                        <td>{{ $evento->descripcion }}</td>
                                        <td>
                                            @if($evento->facturaElectronica)
                                                <a href="{{ route('facturas.show', $evento->facturaElectronica) }}">
                                                    {{ $evento->facturaElectronica->cdc ? Str::limit($evento->facturaElectronica->cdc, 10) : 'Sin CDC' }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No hay eventos recientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
