<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacturacionElectronicaController;
use App\Http\Controllers\FacturacionElectronicaEventosController;
use App\Http\Controllers\Api\FacturacionElectronicaController as FacturacionElectronicaApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas para Facturación Electrónica
Route::prefix('facturacion-electronica')->group(function () {
    // Rutas básicas (versión Node.js - mantenidas para compatibilidad)
    Route::post('/generar-xml', [FacturacionElectronicaController::class, 'generarXML']);
    Route::post('/generar-cdc', [FacturacionElectronicaController::class, 'generarCDC']);
    Route::post('/validar-datos', [FacturacionElectronicaController::class, 'validarDatos']);
    
    // Rutas para la implementación nativa en PHP
    Route::prefix('php')->group(function () {
        Route::post('/generar-xml', [FacturacionElectronicaApiController::class, 'generarXml']);
        Route::post('/generar-cdc', [FacturacionElectronicaApiController::class, 'generarCdc']);
        Route::post('/validar-datos', [FacturacionElectronicaApiController::class, 'validarDatos']);
        Route::post('/generar-xml-evento-cancelacion', [FacturacionElectronicaApiController::class, 'generarXmlEventoCancelacion']);
        Route::post('/generar-xml-evento-inutilizacion', [FacturacionElectronicaApiController::class, 'generarXmlEventoInutilizacion']);
    });
    
    // Rutas de verificación SIFEN
    Route::get('/verificar-documento/{cdc}', [FacturacionElectronicaController::class, 'verificarDocumentoSifen']);
    Route::post('/verificar-documento', [FacturacionElectronicaController::class, 'verificarDocumentoSifen']);
    
    // Rutas para eventos
    Route::post('/eventos/cancelacion', [FacturacionElectronicaEventosController::class, 'generarXmlEventoCancelacion']);
    Route::post('/eventos/inutilizacion', [FacturacionElectronicaEventosController::class, 'generarXmlEventoInutilizacion']);
    Route::post('/eventos/conformidad', [FacturacionElectronicaEventosController::class, 'generarXmlEventoConformidad']);
    Route::post('/eventos/disconformidad', [FacturacionElectronicaEventosController::class, 'generarXmlEventoDisconformidad']);
    Route::post('/eventos/desconocimiento', [FacturacionElectronicaEventosController::class, 'generarXmlEventoDesconocimiento']);
    Route::post('/eventos/notificacion', [FacturacionElectronicaEventosController::class, 'generarXmlEventoNotificacion']);
    
    // Ruta para obtener información de ciudad
    Route::get('/ciudad/{ciudadId}', [FacturacionElectronicaEventosController::class, 'getCiudad']);
});
