@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Eventos de Facturación Electrónica Paraguay</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p>Esta página muestra las diferentes opciones para eventos de facturación electrónica en Paraguay.</p>
                        <p>Los eventos son acciones que se pueden realizar sobre documentos electrónicos ya emitidos.</p>
                    </div>
                    
                    <h4 class="mt-4">Tipos de Eventos</h4>
                    <div class="row mt-3">
                        <!-- Evento de Cancelación -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    Cancelación
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Permite cancelar un documento electrónico emitido.</p>
                                    <form id="cancelacionForm">
                                        <div class="mb-3">
                                            <label for="cdc_cancelacion" class="form-label">CDC del Documento</label>
                                            <input type="text" class="form-control" id="cdc_cancelacion" required placeholder="CDC del documento a cancelar">
                                        </div>
                                        <div class="mb-3">
                                            <label for="motivo_cancelacion" class="form-label">Motivo</label>
                                            <textarea class="form-control" id="motivo_cancelacion" required rows="2" placeholder="Motivo de la cancelación"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Generar XML</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Evento de Inutilización -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    Inutilización
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Permite inutilizar un rango de numeración.</p>
                                    <form id="inutilizacionForm">
                                        <div class="mb-3">
                                            <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                                            <select class="form-select" id="tipo_documento" required>
                                                <option value="1">Factura Electrónica</option>
                                                <option value="4">Autofactura Electrónica</option>
                                                <option value="5">Nota de Crédito Electrónica</option>
                                                <option value="6">Nota de Débito Electrónica</option>
                                                <option value="7">Nota de Remisión Electrónica</option>
                                            </select>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <label for="establecimiento" class="form-label">Establecimiento</label>
                                                <input type="text" class="form-control" id="establecimiento" required placeholder="Ej: 001">
                                            </div>
                                            <div class="col">
                                                <label for="punto" class="form-label">Punto</label>
                                                <input type="text" class="form-control" id="punto" required placeholder="Ej: 001">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <label for="desde" class="form-label">Desde</label>
                                                <input type="number" class="form-control" id="desde" required min="1">
                                            </div>
                                            <div class="col">
                                                <label for="hasta" class="form-label">Hasta</label>
                                                <input type="number" class="form-control" id="hasta" required min="1">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="motivo_inutilizacion" class="form-label">Motivo</label>
                                            <textarea class="form-control" id="motivo_inutilizacion" required rows="2" placeholder="Motivo de la inutilización"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Generar XML</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Evento de Conformidad -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    Conformidad
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Permite expresar conformidad con un documento electrónico recibido.</p>
                                    <form id="conformidadForm">
                                        <div class="mb-3">
                                            <label for="cdc_conformidad" class="form-label">CDC del Documento</label>
                                            <input type="text" class="form-control" id="cdc_conformidad" required placeholder="CDC del documento">
                                        </div>
                                        <div class="mb-3">
                                            <label for="tipo_conformidad" class="form-label">Tipo de Conformidad</label>
                                            <select class="form-select" id="tipo_conformidad" required>
                                                <option value="1">Conformidad</option>
                                                <option value="2">Otra opción</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fecha_recepcion" class="form-label">Fecha de Recepción</label>
                                            <input type="datetime-local" class="form-control" id="fecha_recepcion" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Generar XML</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Evento de Disconformidad -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    Disconformidad
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Permite expresar disconformidad con un documento electrónico recibido.</p>
                                    <form id="disconformidadForm">
                                        <div class="mb-3">
                                            <label for="cdc_disconformidad" class="form-label">CDC del Documento</label>
                                            <input type="text" class="form-control" id="cdc_disconformidad" required placeholder="CDC del documento">
                                        </div>
                                        <div class="mb-3">
                                            <label for="motivo_disconformidad" class="form-label">Motivo</label>
                                            <textarea class="form-control" id="motivo_disconformidad" required rows="2" placeholder="Motivo de la disconformidad"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Generar XML</button>
                                    </form>
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
                                <pre id="resultadoContenido" class="bg-light p-3 rounded"></pre>
                            </div>
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
    // Función para mostrar el resultado
    function mostrarResultado(data) {
        document.getElementById('resultadoContenido').textContent = data;
        document.getElementById('resultado').style.display = 'block';
    }
    
    // Función para manejar errores
    function manejarError(error) {
        document.getElementById('resultadoContenido').textContent = 'Error: ' + error.message;
        document.getElementById('resultado').style.display = 'block';
    }
    
    // Manejador para el formulario de cancelación
    document.getElementById('cancelacionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const data = {
            id: 1,
            params: {}, // Se usarán los valores por defecto del servidor
            data: {
                cdc: document.getElementById('cdc_cancelacion').value,
                motivo: document.getElementById('motivo_cancelacion').value
            }
        };
        
        fetch('/api/facturacion-electronica/eventos/cancelacion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (response.ok) {
                return response.text();
            }
            throw new Error('Error en la respuesta del servidor');
        })
        .then(data => {
            mostrarResultado(data);
        })
        .catch(error => {
            manejarError(error);
        });
    });
    
    // Manejador para el formulario de inutilización
    document.getElementById('inutilizacionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const data = {
            id: 2,
            params: {}, // Se usarán los valores por defecto del servidor
            data: {
                tipoDocumento: parseInt(document.getElementById('tipo_documento').value),
                establecimiento: document.getElementById('establecimiento').value,
                punto: document.getElementById('punto').value,
                desde: parseInt(document.getElementById('desde').value),
                hasta: parseInt(document.getElementById('hasta').value),
                motivo: document.getElementById('motivo_inutilizacion').value
            }
        };
        
        fetch('/api/facturacion-electronica/eventos/inutilizacion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (response.ok) {
                return response.text();
            }
            throw new Error('Error en la respuesta del servidor');
        })
        .then(data => {
            mostrarResultado(data);
        })
        .catch(error => {
            manejarError(error);
        });
    });
    
    // Manejador para el formulario de conformidad
    document.getElementById('conformidadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const data = {
            id: 3,
            params: {}, // Se usarán los valores por defecto del servidor
            data: {
                cdc: document.getElementById('cdc_conformidad').value,
                tipoConformidad: parseInt(document.getElementById('tipo_conformidad').value),
                fechaRecepcion: document.getElementById('fecha_recepcion').value
            }
        };
        
        fetch('/api/facturacion-electronica/eventos/conformidad', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (response.ok) {
                return response.text();
            }
            throw new Error('Error en la respuesta del servidor');
        })
        .then(data => {
            mostrarResultado(data);
        })
        .catch(error => {
            manejarError(error);
        });
    });
    
    // Manejador para el formulario de disconformidad
    document.getElementById('disconformidadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const data = {
            id: 4,
            params: {}, // Se usarán los valores por defecto del servidor
            data: {
                cdc: document.getElementById('cdc_disconformidad').value,
                motivo: document.getElementById('motivo_disconformidad').value
            }
        };
        
        fetch('/api/facturacion-electronica/eventos/disconformidad', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (response.ok) {
                return response.text();
            }
            throw new Error('Error en la respuesta del servidor');
        })
        .then(data => {
            mostrarResultado(data);
        })
        .catch(error => {
            manejarError(error);
        });
    });
</script>
@endsection
