<?php

use Illuminate\Support\Facades\Route;

use Modules\Compras\Http\Controllers\CatUnidadesMedidaController;
use Modules\Compras\Http\Controllers\ProveedoresController;
use Modules\Compras\Http\Controllers\SolicitudesCompraController;
use Modules\Compras\Http\Controllers\ExpedientesProveedoresController;
use Modules\Compras\Http\Controllers\CotizacionesController;
use Modules\Compras\Http\Controllers\OrdenesComprasController;
use Modules\Compras\Http\Controllers\DocumentosOrdenesComprasController;
use Modules\Compras\Http\Controllers\CatUnidadesController;
use Modules\Compras\Http\Controllers\SolicitudesMacroController;
use Modules\Compras\Models\Cotizaciones;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Compras\Http\Controllers\UsuariosController;
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Http\Controllers\DetalleSolicitudController;

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
    Route::resource('CatalogoUnidades', CatUnidadesController::class);
    Route::resource('CatalogoUnidadesMedida', CatUnidadesMedidaController::class);
    Route::resource('Proveedores', ProveedoresController::class);
    Route::resource('SolicitudesCompras', SolicitudesCompraController::class);
    Route::resource('ExpedientesProveedores', ExpedientesProveedoresController::class);
    Route::resource('Cotizaciones', CotizacionesController::class);
    Route::resource('OrdenesCompras', OrdenesComprasController::class);
    Route::resource('DocumentosOrdenesCompras', DocumentosOrdenesComprasController::class);
    Route::resource('Usuarios', UsuariosController::class);
    Route::resource('SolicitudesMacro', SolicitudesMacroController::class);
    Route::resource('DetallesSolicitud', DetalleSolicitudController::class);
    
    Route::get('/Solicitudes/{intercompania}',[SolicitudesCompraController::class, 'index'])->name('SolicitudesCompras.solicitudes');
    Route::get('/Solicitudes/Macro/{intercompania}',[SolicitudesMacroController::class, 'index'])->name('SolicitudesMacro.solicitudes');
    Route::get('/getProveedores',[ProveedoresController::class, 'getProveedores'])->name('Proveedores.getProveedores');
    Route::get('/SolicitudCompra/{id}',[SolicitudesCompraController::class, 'getSolicitud'])->name('SolicitudesCompras.getSolicitud');

    //*Ruta para mostra archivos 
    Route::get('expedientes/{id}/{file}', [ExpedientesProveedoresController::class, 'getFile'])->name('ExpedientesProveedores.getFile');
    Route::get('cotizaciones/{id}/{file}', [CotizacionesController::class, 'getFile'])->name('Cotizaciones.getFile');
    Route::get('docsOrdenCompra/{id}/{file}', [DocumentosOrdenesComprasController::class, 'getFile'])->name('DocumentosOrdenesCompras.getFile');

    //*Ruta para enviar un email de prueba, no funciona pero va funcionar
    Route::post('/enviar-solicitud-cotizacion', [SolicitudesCompraController::class, 'enviarSolicitudCotizacion'])->name('SolicitudesCompra.enviarSolicitudCotizacion');
    Route::post('/enviar-solicitud-surtido', [OrdenesComprasController::class, 'enviarSolicitudSurtido'])->name('OrdenesCompras.enviarSolicitudSurtido');
    Route::post('/autorizar-orden-compra', [OrdenesComprasController::class, 'autorizarOrden'])->name('OrdenesCompras.autorizarOrden');

    //Route::get('/autorizacion-solicitud-gerencia/{id}', [SolicitudesCompraController::class, 'autorizeFromEmail']);
    Route::get('/autorizacion-solicitud-gerencia/{campo}/{necesarias}/{id}', [SolicitudesCompraController::class, 'autorizeFromEmail'])->name('confirm.accion')->middleware('signed');
    //*Ruta para generar pdf
    Route::get('/consulta-datos-pdf/{id}',[OrdenesComprasController::class, 'consultaDatosPDF'])->name('OrdenesCompras.consultaDatosPDF');
    Route::put('/autorizar-cotizacion/{id}',[CotizacionesController::class, 'autorizarCotizacion'])->name('Cotizaciones.autorizarCotizacion');
    Route::get('/solicitar-autorizacion/{id}',[CotizacionesController::class, 'limpiarAutorizaciones'])->name('Cotizaciones.solicitaAutorizacion');

    //*Ruta para leer el XML
    Route::get('/leer-xml/{id}',[OrdenesComprasController::class, 'leerXML'])->name('OrdenesCompras.leerXML');
    Route::get('/get-data-xml/{id}', [DocumentosOrdenesComprasController::class, 'leerYProcesarXML'])->name('OrdenesCompras.leerYProcesarXML');
    //*Rutas descarga zip
    Route::get('/descargar-facturas/{id}',[DocumentosOrdenesComprasController::class, 'downloadFacturas'])->name('DocumentosOrdenesCompras.downloadFacturas');
    Route::get('/descargar-expediente/{id}',[ExpedientesProveedoresController::class, 'downloadExpediente'])->name('ExpedientesProveedores.downloadExpediente');

    Route::get('/getUserByEmail/{correo}', [UsuariosController::class, 'getDataUsuario'])->name('Usuarios.getDataUsuario');
    Route::get('/getUserById/{correo}', [UsuariosController::class, 'showById'])->name('Usuarios.showById');

    //*Rutas para el catalogo de vehiculos
    Route::post('/importar-autotanques', [CatUnidadesController::class, 'importarCSV']);
    Route::get('/recuperar-autotanques/{intercompania}', [CatUnidadesController::class, 'getAutotanques']);
    Route::get('/recuperar-gastos-vehiculo/{id}', [CatUnidadesController::class, 'getGastoUnidad']);
});

