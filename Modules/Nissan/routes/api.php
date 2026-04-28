<?php

use Illuminate\Support\Facades\Route;
use Modules\Nissan\Http\Controllers\ComisionesController;
use Modules\Nissan\Http\Controllers\CompraSeminuevosController;
use Modules\Nissan\Http\Controllers\CompraSeminuevosPDFController;
use Modules\Nissan\Http\Controllers\DatosVentaController;
use Modules\Nissan\Http\Controllers\NissanController;
use Modules\Nissan\Http\Controllers\TipoVentaController;
use Modules\Nissan\Http\Controllers\VendedorController;

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
//Route::apiResource('nissan', NissanController::class)->names('nissan');

// Route::prefix('nissan')->group(function () {
Route::middleware(['auth:sanctum'])->prefix('nissan')->group(function () {
    Route::apiResource('comisiones', ComisionesController::class)->names('comisiones');
    Route::apiResource('datos-venta', DatosVentaController::class)->names('datos-venta');
    Route::apiResource('vendedor', VendedorController::class)->names('vendedor');
    Route::apiResource('tipo-venta', TipoVentaController::class)->names('tipo-venta');
    Route::apiResource('compra-seminuevos', CompraSeminuevosController::class)->names('compra-seminuevos');
    Route::apiResource('compra-seminuevos-pdf', CompraSeminuevosPDFController::class)->names('compra-seminuevos-pdf');

    Route::post('datos-venta/validados', [DatosVentaController::class, 'storeValidados'])->name('datos-venta-validados');
    Route::post('datos-venta/bdc', [DatosVentaController::class, 'updatePartidaBDC'])->name('datos-venta-bdc');
    Route::post('datos-venta/validados_bdc', [DatosVentaController::class, 'updatePartidaValidacionBDC'])->name('datos-venta-validados-bdc');
    Route::get('datos-venta/pagado/{id}', [DatosVentaController::class, 'updatePartidaPagado'])->name('datos-venta-validados');
});

Route::get('nissan/comisiones/{f_inicial}/{f_final}', [ComisionesController::class, 'index'])->name('nissan-comisiones.index');
Route::get('nissan/porcentajes', [ComisionesController::class, 'getPorcentajes'])->name('nissan-comisiones.getPorcentajes');

Route::get('autos/libro-ventas/{estatus}/{agencia}/{tipoVenta}/{fechaInicio}/{fechaFin}/{vendedor}', [ComisionesController::class, 'getDatosVentas'])->name('autos-comisiones.getLibroVentas');
Route::get('autos/descarga-libro-ventas/{estatus}/{agencia}/{tipoVenta}/{fechaInicio}/{fechaFin}/{vendedor}', [ComisionesController::class, 'downloadReporte'])->name('autos-comisiones.downloadLibroVentas');
Route::post('/cargar-csv', [VendedorController::class, 'importCsv']);

