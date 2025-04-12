<?php

use Illuminate\Support\Facades\Route;

use Modules\Compras\Http\Controllers\CatUnidadesMedidaController;
use Modules\Compras\Http\Controllers\ProveedoresController;
use Modules\Compras\Http\Controllers\SolicitudesCompraController;
use Modules\Compras\Http\Controllers\ExpedientesProveedoresController;
use Modules\Compras\Http\Controllers\CotizacionesController;
use Modules\Compras\Http\Controllers\OrdenesComprasController;
use Modules\Compras\Http\Controllers\DocumentosOrdenesComprasController;
use Modules\Compras\Models\Cotizaciones;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Compras\Http\Controllers\UsuariosController;
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
    Route::resource('DocumentosOrdenesCompras', DocumentosOrdenesComprasController::class);
    Route::resource('Usuarios', UsuariosController::class);
    
    Route::get('/getProveedores',[ProveedoresController::class, 'getProveedores']);
    Route::get('/SolicitudCompra/{id}',[SolicitudesCompraController::class, 'getSolicitud']);

    //*Ruta para mostra archivos 
    Route::get('expedientes/{id}/{file}', [ExpedientesProveedoresController::class, 'getFile']);
    Route::get('cotizaciones/{id}/{file}', [CotizacionesController::class, 'getFile']);
    Route::get('docsOrdenCompra/{id}/{file}', [DocumentosOrdenesComprasController::class, 'getFile']);

    //*Ruta para enviar un email de prueba, no funciona pero va funcionar
    Route::post('/enviar-solicitud-cotizacion', [SolicitudesCompraController::class, 'enviarSolicitudCotizacion']);
    Route::post('/enviar-solicitud-surtido', [OrdenesComprasController::class, 'enviarSolicitudSurtido']);
    Route::post('/autorizar-orden-compra', [OrdenesComprasController::class, 'autorizarOrden']);

    //*Ruta para generar pdf
    Route::get('/consulta-datos-pdf/{id}',[OrdenesComprasController::class, 'consultaDatosPDF']);

    //*Ruta para leer el XML
    Route::get('/leer-xml/{id}',[OrdenesComprasController::class, 'leerXML']);

    //*Rutas descarga zip
    Route::get('/descargar-facturas/{id}',[DocumentosOrdenesComprasController::class, 'downloadFacturas']);
    Route::get('/descargar-expediente/{id}',[ExpedientesProveedoresController::class, 'downloadExpediente']);

    Route::get('/getUserByEmail/{correo}', [UsuariosController::class, 'getDataUsuario']);

});

