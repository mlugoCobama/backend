<?php

use Illuminate\Support\Facades\Route;
use Modules\TarjetaClientes\Http\Controllers\TarjetaClientesController;

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
prefix('cpp')->group(function () {
    Route::apiResource('tarjetaclientes', TarjetaClientesController::class)->names('tarjetaclientes');
});
