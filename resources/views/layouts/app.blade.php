<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Facturación Electrónica Paraguay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">Facturación Electrónica PY</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('facturas.*') ? 'active' : '' }}" href="{{ route('facturas.index') }}">
                            <i class="bi bi-file-earmark-text"></i> Facturas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('facturacion.index') ? 'active' : '' }}" href="{{ route('facturacion.index') }}">
                            <i class="bi bi-file-code"></i> Generación de XML
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('facturacion.eventos') ? 'active' : '' }}" href="{{ route('facturacion.eventos') }}">
                            <i class="bi bi-lightning-charge"></i> Eventos
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>
    
    <footer class="bg-light py-3 mt-5">
        <div class="container">
            <div class="text-center">
                <p class="mb-0">&copy; {{ date('Y') }} Facturación Electrónica Paraguay</p>
                <p class="mb-0 small">Implementación para SIFEN - Paraguay</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
