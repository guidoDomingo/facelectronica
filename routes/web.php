<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EjemploFacturacionController;
use App\Http\Controllers\FacturasElectronicasController;
use App\Http\Controllers\FacturacionElectronicaEventosController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('facturas.index');
});

// Rutas para el ejemplo de Facturación Electrónica
Route::get('/facturacion', [EjemploFacturacionController::class, 'index'])->name('facturacion.index');
Route::get('/facturacion/generar-ejemplo', [EjemploFacturacionController::class, 'generarEjemplo'])->name('facturacion.generar-ejemplo');
Route::post('/facturacion/generar-cdc-ejemplo', [EjemploFacturacionController::class, 'generarCDCEjemplo'])->name('facturacion.generar-cdc-ejemplo');
Route::get('/facturacion/eventos', [EjemploFacturacionController::class, 'eventos'])->name('facturacion.eventos');

// Rutas para la gestión de Facturas Electrónicas
Route::resource('facturas', FacturasElectronicasController::class);
Route::get('facturas/{factura}/descargar-xml', [FacturasElectronicasController::class, 'descargarXml'])->name('facturas.descargar-xml');
Route::post('facturas/{factura}/cancelar', [FacturasElectronicasController::class, 'cancelar'])->name('facturas.cancelar');

// Ruta para mostrar el QR de una factura
Route::get('facturas/{factura}/qr', [\App\Http\Controllers\QRCodeController::class, 'mostrarQR'])->name('facturas.qr');

// Rutas para verificación y envío a SIFEN
Route::get('facturas/{factura}/verificar-sifen', [\App\Http\Controllers\FacturaVerificacionController::class, 'verificarEstadoSIFEN'])->name('facturas.verificar-sifen');
Route::post('facturas/{factura}/enviar-sifen', [\App\Http\Controllers\FacturaVerificacionController::class, 'enviarASIFEN'])->name('facturas.enviar-sifen');

// Rutas para la gestión de Eventos de Facturación Electrónica
Route::post('/eventos/cancelacion', [FacturacionElectronicaEventosController::class, 'cancelacion'])->name('eventos.cancelacion');
Route::post('/eventos/inutilizacion', [FacturacionElectronicaEventosController::class, 'inutilizacion'])->name('eventos.inutilizacion');
Route::post('/eventos/conformidad', [FacturacionElectronicaEventosController::class, 'conformidad'])->name('eventos.conformidad');
Route::post('/eventos/disconformidad', [FacturacionElectronicaEventosController::class, 'disconformidad'])->name('eventos.disconformidad');

/*
|--------------------------------------------------------------------------
| SIFEN Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin/sifen')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\SifenDashboardController::class, 'index'])->name('admin.sifen.dashboard');
    Route::get('/pendientes', [App\Http\Controllers\SifenDashboardController::class, 'pendientes'])->name('admin.sifen.pendientes');
    Route::get('/errores', [App\Http\Controllers\SifenDashboardController::class, 'errores'])->name('admin.sifen.errores');
    Route::get('/certificado', [App\Http\Controllers\SifenDashboardController::class, 'certificado'])->name('admin.sifen.certificado');
    
    // Acciones sobre facturas
    Route::post('/verificar/{factura}', [App\Http\Controllers\SifenDashboardController::class, 'verificarEstado'])->name('admin.sifen.verificar');
    Route::post('/reintentar/{factura}', [App\Http\Controllers\SifenDashboardController::class, 'reintentarEnvio'])->name('admin.sifen.reintentar');
    
    // Gestión de certificados
    Route::post('/importar-certificado', [App\Http\Controllers\SifenDashboardController::class, 'importarCertificado'])->name('admin.sifen.importar-certificado');
});
