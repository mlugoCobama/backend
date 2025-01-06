<?php

use Illuminate\Support\Facades\Route;
use Modules\Compras\Http\Controllers\ComprasController;

use Modules\Compras\Http\Controllers\ProveedoresController;
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

//Route::middleware(['auth:sanctum'])->group(function () {
Route::prefix('compras')->group(function () {
    Route::apiResource('compras', ComprasController::class)->names('compras');
    Route::apiResource('proveedores', ProveedoresController::class)->names('proveedores');
});

