@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Crear Nueva Factura Electrónica</h4>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('facturas.store') }}" method="POST" id="facturaForm">
                        @csrf
                        <div class="row">
                            <!-- Datos del documento -->
                            <div class="col-md-6">
                                <h5>Datos del Documento</h5>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                                        <select name="tipo_documento" id="tipo_documento" class="form-select @error('tipo_documento') is-invalid @enderror" required>
                                            <option value="">Seleccione...</option>
                                            <option value="1" {{ old('tipo_documento') == 1 ? 'selected' : '' }}>Factura Electrónica</option>
                                            <option value="4" {{ old('tipo_documento') == 4 ? 'selected' : '' }}>Autofactura Electrónica</option>
                                            <option value="5" {{ old('tipo_documento') == 5 ? 'selected' : '' }}>Nota de Crédito Electrónica</option>
                                            <option value="6" {{ old('tipo_documento') == 6 ? 'selected' : '' }}>Nota de Débito Electrónica</option>
                                            <option value="7" {{ old('tipo_documento') == 7 ? 'selected' : '' }}>Nota de Remisión Electrónica</option>
                                        </select>
                                        @error('tipo_documento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="moneda" class="form-label">Moneda</label>
                                        <select name="moneda" id="moneda" class="form-select @error('moneda') is-invalid @enderror" required>
                                            <option value="PYG" {{ old('moneda') == 'PYG' ? 'selected' : '' }}>Guaraní (PYG)</option>
                                            <option value="USD" {{ old('moneda') == 'USD' ? 'selected' : '' }}>Dólar (USD)</option>
                                        </select>
                                        @error('moneda')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="establecimiento" class="form-label">Establecimiento</label>
                                        <input type="text" name="establecimiento" id="establecimiento" class="form-control @error('establecimiento') is-invalid @enderror" 
                                            value="{{ old('establecimiento', '001') }}" required maxlength="3" pattern="[0-9]{3}">
                                        @error('establecimiento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="punto" class="form-label">Punto</label>
                                        <input type="text" name="punto" id="punto" class="form-control @error('punto') is-invalid @enderror" 
                                            value="{{ old('punto', '001') }}" required maxlength="3" pattern="[0-9]{3}">
                                        @error('punto')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="numero" class="form-label">Número</label>
                                        <input type="text" name="numero" id="numero" class="form-control @error('numero') is-invalid @enderror" 
                                            value="{{ old('numero', '0000001') }}" required maxlength="7" pattern="[0-9]{7}">
                                        @error('numero')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="observacion" class="form-label">Observación</label>
                                    <textarea name="observacion" id="observacion" class="form-control @error('observacion') is-invalid @enderror" rows="2">{{ old('observacion') }}</textarea>
                                    @error('observacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Datos del receptor -->
                            <div class="col-md-6">
                                <h5>Datos del Receptor</h5>
                                <hr>
                                <div class="mb-3">
                                    <label for="ruc_receptor" class="form-label">RUC</label>
                                    <input type="text" name="ruc_receptor" id="ruc_receptor" class="form-control @error('ruc_receptor') is-invalid @enderror" 
                                        value="{{ old('ruc_receptor') }}" required>
                                    @error('ruc_receptor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="razon_social_receptor" class="form-label">Razón Social</label>
                                    <input type="text" name="razon_social_receptor" id="razon_social_receptor" class="form-control @error('razon_social_receptor') is-invalid @enderror" 
                                        value="{{ old('razon_social_receptor') }}" required>
                                    @error('razon_social_receptor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="direccion_receptor" class="form-label">Dirección</label>
                                    <input type="text" name="direccion_receptor" id="direccion_receptor" class="form-control @error('direccion_receptor') is-invalid @enderror" 
                                        value="{{ old('direccion_receptor', 'Dirección de ejemplo') }}">
                                    @error('direccion_receptor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Detalle de items -->
                        <h5 class="mt-4">Detalle de Items</h5>
                        <hr>
                        <div id="items-container">
                            <div class="item-row row mb-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">Descripción</label>
                                    <input type="text" name="items[0][descripcion]" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" name="items[0][cantidad]" class="form-control item-cantidad" min="1" value="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Precio</label>
                                    <input type="number" name="items[0][precio_unitario]" class="form-control item-precio" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">IVA %</label>
                                    <select name="items[0][iva]" class="form-select item-iva">
                                        <option value="10">10%</option>
                                        <option value="5">5%</option>
                                        <option value="0">Exento</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-item" style="display: none;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="button" id="add-item" class="btn btn-sm btn-secondary">
                                <i class="bi bi-plus"></i> Agregar Item
                            </button>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Resumen</h6>
                                        <div class="d-flex justify-content-between">
                                            <span>Subtotal:</span>
                                            <span id="subtotal">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>IVA:</span>
                                            <span id="total-iva">0</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Total:</span>
                                            <span id="total">0</span>
                                        </div>
                                        <input type="hidden" name="total" id="total-input" value="0">
                                        <input type="hidden" name="impuesto" id="impuesto-input" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('facturas.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Generar Factura</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let itemIndex = 0;
        
        // Agregar nuevo item
        document.getElementById('add-item').addEventListener('click', function() {
            itemIndex++;
            
            const newItem = document.querySelector('.item-row').cloneNode(true);
            
            // Actualizar los índices en los nombres de los campos
            newItem.querySelectorAll('input, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/\[\d+\]/, `[${itemIndex}]`));
                    // Limpiar valores
                    if (input.classList.contains('item-precio')) {
                        input.value = '';
                    } else if (input.classList.contains('item-cantidad')) {
                        input.value = '1';
                    }
                }
            });
            
            // Mostrar botón de eliminar
            newItem.querySelector('.remove-item').style.display = 'block';
            
            document.getElementById('items-container').appendChild(newItem);
            
            // Agregar evento para eliminar item
            newItem.querySelector('.remove-item').addEventListener('click', function() {
                newItem.remove();
                calcularTotales();
            });
            
            // Agregar eventos de recalcular
            newItem.querySelectorAll('.item-cantidad, .item-precio, .item-iva').forEach(input => {
                input.addEventListener('change', calcularTotales);
            });
        });
        
        // Mostrar botón de eliminar para el primer item si se agrega otro
        document.getElementById('add-item').addEventListener('click', function() {
            const items = document.querySelectorAll('.item-row');
            if (items.length > 1) {
                items[0].querySelector('.remove-item').style.display = 'block';
            }
        });
        
        // Calcular totales cuando se modifican los valores
        document.querySelectorAll('.item-cantidad, .item-precio, .item-iva').forEach(input => {
            input.addEventListener('change', calcularTotales);
        });
        
        function calcularTotales() {
            let subtotal = 0;
            let totalIva = 0;
            
            document.querySelectorAll('.item-row').forEach(row => {
                const cantidad = parseFloat(row.querySelector('.item-cantidad').value) || 0;
                const precio = parseFloat(row.querySelector('.item-precio').value) || 0;
                const ivaPct = parseFloat(row.querySelector('.item-iva').value) || 0;
                
                const itemSubtotal = cantidad * precio;
                const itemIva = itemSubtotal * (ivaPct / 100);
                
                subtotal += itemSubtotal;
                totalIva += itemIva;
            });
            
            const total = subtotal;
            const moneda = document.getElementById('moneda').value;
            
            document.getElementById('subtotal').textContent = formatNumber(subtotal) + ' ' + moneda;
            document.getElementById('total-iva').textContent = formatNumber(totalIva) + ' ' + moneda;
            document.getElementById('total').textContent = formatNumber(total) + ' ' + moneda;
            
            document.getElementById('total-input').value = total.toFixed(2);
            document.getElementById('impuesto-input').value = totalIva.toFixed(2);
        }
        
        function formatNumber(number) {
            return number.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
        
        // Cambiar formato de números al cambiar moneda
        document.getElementById('moneda').addEventListener('change', calcularTotales);
    });
</script>
@endsection
