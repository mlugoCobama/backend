<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use Modules\Nissan\Http\Controllers\AccesorioController;
use Modules\Nissan\Http\Controllers\ConcentradoComisionesController;
use Modules\Renault\Http\Controllers\RenaultController;
use Modules\Renault\Http\Controllers\VisorCitasController;
use Modules\Nissan\Http\Controllers\FinanciamientoController;
use Modules\Nissan\Http\Controllers\OtrosController;
use Modules\Nissan\Http\Controllers\SegurosController;
use Modules\Nissan\Http\Controllers\TomaUnidadController;

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

//Route::middleware(['auth:sanctum'])->prefix('renault')->group(function () {
Route::prefix('renault')->group(function () {
    Route::apiResource('visor-citas', VisorCitasController::class)->names('visor-citas');
    Route::apiResource('financiamiento', FinanciamientoController::class)->names('nrfinance');
    Route::apiResource('toma-unidad', TomaUnidadController::class)->names('toma-unida');
    Route::apiResource('seguro', SegurosController::class)->names('seguro');
    Route::apiResource('accesorios', AccesorioController::class)->names('accesorios');
    Route::apiResource('concentrado-comisiones', ConcentradoComisionesController::class)->names('concentrado-comisiones');
    Route::apiResource('otros', OtrosController::class)->names('otros');

    Route::get('visor-citas/datos-ingreso/{id}', [ VisorCitasController::class, 'getDatosIngreso'])->name('visor-citas.getDatosEntrada');
    Route::post('auth/login', [LoginController::class, 'loginMobile']);
    Route::get('visor-citas/orden-servicio/{id}', [ VisorCitasController::class, 'descargarPdfOrdenServicio'])->name('visor-citas.descargarOrdenServicio');
    Route::get('visor-citas/{intercompania}/{apv}/{fechaInicial}/{fechaFinal}', [ VisorCitasController::class, 'datosFiltrados'])->name('visor-citas.datosFiltrados');
    Route::get('aps/{idAgencia}', [ VisorCitasController::class, 'getApsByAgencia'])->name('visor-citas.getApsByAgencia');
    
    Route::get('financiamiento/datos-venta/{factura}', [ FinanciamientoController::class, 'getDataVenta'])->name('financiamineto.getDataVentaFin');
    Route::get('financiamiento/{estatus}/{agencia}/{fechaInicio}/{fechaFin}/{vendedor}', [FinanciamientoController::class, 'getFinanciaminetos'])->name('financiamineto.getFinanciaminetos');
    Route::get('financiamiento/avanzarEstatus/{id}', [FinanciamientoController::class, 'avanzarEstatus'])->name('financiamineto.avanzarEstatus');

    Route::get('toma-unidad/datos-venta/{factura}', [ TomaUnidadController::class, 'getDataVenta'])->name('toma-unidad.getDataVentaFin');
    Route::get('toma-unidad/{estatus}/{agencia}/{fechaInicio}/{fechaFin}/{vendedor}', [TomaUnidadController::class, 'getTomaUnidad'])->name('toma-unidad.getTomaUnidad');
    Route::get('toma-unidad/avanzarEstatus/{id}', [TomaUnidadController::class, 'avanzarEstatus'])->name('toma-unidad.avanzarEstatus');

    Route::get('seguro/datos-venta/{factura}', [ SegurosController::class, 'getDataVenta'])->name('seguro.getDataVentaFin');
    Route::get('seguro/{estatus}/{agencia}/{fechaInicio}/{fechaFin}/{vendedor}', [SegurosController::class, 'getTomaUnidad'])->name('seguro.getTomaUnidad');
    Route::get('seguro/avanzarEstatus/{id}', [SegurosController::class, 'avanzarEstatus'])->name('seguro.avanzarEstatus');

    Route::get('accesorios/datos-venta/{factura}', [ AccesorioController::class, 'getDataVenta'])->name('accesorios.getDataVentaFin');
    Route::get('accesorios/{estatus}/{agencia}/{fechaInicio}/{fechaFin}/{vendedor}', [AccesorioController::class, 'getTomaUnidad'])->name('accesorios.getTomaUnidad');
    Route::get('accesorios/avanzarEstatus/{id}', [AccesorioController::class, 'avanzarEstatus'])->name('accesorios.avanzarEstatus');

    Route::get('concentrado-comisiones/detalle/{idVendedor}/{rubro}', [ConcentradoComisionesController::class, 'viewDetallesVendedorRubro'])->name('concentrado-comisiones.viewDetallesVendedorRubro');
    Route::put('concentrado-comisiones/devolver/{id}', [ConcentradoComisionesController::class, 'devolverPendiente'])->name('concentrado-comisiones.devolverPendiente');
    Route::put('concentrado-comisiones/autorizado/{id}', [ConcentradoComisionesController::class, 'autorizadoPendiente'])->name('concentrado-comisiones.autorizadoPendiente');

    Route::get('concentrado-comisiones/listado/cortes/{agencia}', [ConcentradoComisionesController::class, 'getListadoCortes'])->name('concentrado-comisiones.getCorte');
    Route::get('concentrado-comisiones/corte/{id}', [ConcentradoComisionesController::class, 'getCorte'])->name('concentrado-comisiones.getListadoCortes');
    });

Route::get('storage/renault/citas_servicio/{fileName}', [VisorCitasController::class, 'getFile'])->name('visor-citas.getFile');
Route::get('renault/financiamiento/archivo/{id}', [FinanciamientoController::class, 'mostrarArchivo'])->name('financiamiento.mostrarArchivo');
Route::get('renault/seguro/archivo/{id}', [SegurosController::class, 'mostrarArchivo'])->name('seguro.mostrarArchivo');