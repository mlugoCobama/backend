<?php

use Illuminate\Support\Facades\Route;

use Modules\Compras\Http\Controllers\CatUnidadesMedidaController;
use Modules\Compras\Http\Controllers\ProveedoresController;
use Modules\Compras\Http\Controllers\SolicitudesCompraController;
use Modules\Compras\Http\Controllers\ExpedientesProveedoresController;
use Modules\Compras\Http\Controllers\CotizacionesController;
use Modules\Compras\Http\Controllers\OrdenesComprasController;
use Modules\Compras\Models\Cotizaciones;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Compras\Models\SolicitudesCompra;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

// Route::middleware(['auth:sanctum'])->group(function () {
Route::prefix('compras')->group(function () {
    Route::resource('CatalogoUnidadesMedida', CatUnidadesMedidaController::class);
    Route::resource('Proveedores', ProveedoresController::class);
    Route::resource('SolicitudesCompras', SolicitudesCompraController::class);
    Route::resource('ExpedientesProveedores', ExpedientesProveedoresController::class);
    Route::resource('Cotizaciones', CotizacionesController::class);
    Route::resource('OrdenesCompras', OrdenesComprasController::class);
    //aqui van los resouce de de compras

    //*Ruta para mostra archivos 
    Route::get('expedientes/{id}/{file}', [ExpedientesProveedoresController::class, 'getFile']);
    Route::get('cotizaciones/{id}/{file}', [CotizacionesController::class, 'getFile']);
    //*Ruta para enviar un email de prueba, no funciona :( pero va funcionar
    Route::post('/enviar-solicitud-cotizacion', [SolicitudesCompraController::class, 'enviarSolicitudCotizacion']);
    Route::post('/enviar-solicitud-surtido', [OrdenesComprasController::class, 'enviarSolicitudSurtido']);

    // Ruta para generar pdf
    Route::get('/consulta-datos-pdf/{id}',[OrdenesComprasController::class, 'consultaDatosPDF']);
    Route::post('/descargar-pdf',[OrdenesComprasController::class, 'generarPDFOc']);


    Route::get('/generar-folio',[OrdenesComprasController::class, 'generarFolio']);
    Route::get('/generar-folio-sc',[SolicitudesCompraController::class, 'generarFolioSc']);
    Route::get('/generar-folio-co',[CotizacionesController::class, 'generarFolioCo']);


});

