@extends('layouts.app')

@section('title', 'Gestión de Certificado Digital')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Gestión de Certificado Digital - SIFEN</h5>
                    <div>
                        <a href="{{ route('admin.sifen.dashboard') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Estado del Certificado Digital</h5>
                </div>
                <div class="card-body">
                    @if($certificadoInfo)
                        <div class="alert {{ $habilitado ? 'alert-success' : 'alert-warning' }}">
                            <i class="{{ $habilitado ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle' }}"></i>
                            La firma digital está {{ $habilitado ? 'habilitada' : 'deshabilitada' }} en la configuración.
                        </div>
                        
                        <table class="table table-striped">
                            <tr>
                                <th>Ruta del Certificado:</th>
                                <td>{{ $rutaCertificado }}</td>
                            </tr>
                            <tr>
                                <th>Sujeto:</th>
                                <td>{{ $certificadoInfo['subject'] }}</td>
                            </tr>
                            <tr>
                                <th>Emisor:</th>
                                <td>{{ $certificadoInfo['issuer'] }}</td>
                            </tr>
                            <tr>
                                <th>Número de Serie:</th>
                                <td>{{ $certificadoInfo['serial_number'] }}</td>
                            </tr>
                            <tr>
                                <th>Huella Digital:</th>
                                <td><code>{{ $certificadoInfo['fingerprint'] }}</code></td>
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
                                            ¡EXPIRA PRONTO! {{ $certificadoInfo['days_remaining'] }} días restantes
                                        </span>
                                    @elseif($certificadoInfo['days_remaining'] <= 90)
                                        <span class="badge bg-warning">
                                            {{ $certificadoInfo['days_remaining'] }} días restantes
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            {{ $certificadoInfo['days_remaining'] }} días restantes
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i>
                            No se encontró un certificado digital válido o la configuración es incorrecta.
                        </div>
                        <p>
                            Verifique que el archivo del certificado exista en la ruta configurada y 
                            que la contraseña sea correcta.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Importar Nuevo Certificado Digital</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sifen.importar-certificado') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="certificado" class="form-label">Archivo de Certificado (.p12/.pfx)</label>
                            <input type="file" class="form-control @error('certificado') is-invalid @enderror" 
                                id="certificado" name="certificado" accept=".p12,.pfx" required>
                            @error('certificado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                El archivo debe estar en formato PKCS#12 (.p12 o .pfx)
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="clave" class="form-label">Contraseña del Certificado</label>
                            <input type="password" class="form-control @error('clave') is-invalid @enderror" 
                                id="clave" name="clave" required>
                            @error('clave')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Importar Certificado
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Configuración del Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Las siguientes configuraciones deben establecerse en el archivo <code>.env</code> de su aplicación.
                    </div>

                    <pre class="bg-light p-3 mb-0">
# Configuración de firma digital
FACTURACION_FIRMA_HABILITADA=true
FACTURACION_CERT_PATH={{ $rutaCertificado ?? storage_path('app/certificados/certificado.p12') }}
FACTURACION_CERT_CLAVE=su_contraseña_aquí
                    </pre>

                    <p class="mt-3">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        <b>Nota importante:</b> Después de modificar estas configuraciones, puede ser necesario reiniciar 
                        la aplicación o limpiar la caché con <code>php artisan config:clear</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
