<?php

use Illuminate\Support\Facades\Route;
use Modules\ParqueVehicular\Http\Controllers\EventosOrdenController;
use Modules\ParqueVehicular\Http\Controllers\OrdenesServicioController;
use Modules\ParqueVehicular\Http\Controllers\ParqueVehicularController;
use Modules\ParqueVehicular\Http\Controllers\TrabajosController;
use Modules\ParqueVehicular\Http\Controllers\VehiculoProveedorController;

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

Route::
// middleware(['auth:sanctum'])->
prefix('parque-vehicular')->group(function () {
    Route::apiResource('parquevehicular', ParqueVehicularController::class)->names('parquevehicular');
    Route::apiResource('eventos-orden-servicio', EventosOrdenController::class)->names('eventos-orden-servicio');
    Route::apiResource('vehiculos-proveedores', VehiculoProveedorController::class)->names('vehiculos-proveedores');
    Route::apiResource('trabajos-orden', TrabajosController::class)->names('trabajos-orden');
    Route::apiResource('ordenes-servicio', OrdenesServicioController::class)->names('ordenes-servicio');

    Route::post('vehiculos-proveedores/autorizacion', [VehiculoProveedorController::class, 'autorizarOrdenServicio'])->name('vehiculos-proveedores.autorizarOrdenServicio');
    Route::post('vehiculos-proveedores/finalizar', [VehiculoProveedorController::class, 'finalizarOrden'])->name('vehiculos-proveedores.finalizarOrden');

});
