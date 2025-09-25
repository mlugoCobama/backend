<?php

use Illuminate\Support\Facades\Route;
use Modules\Capacitaciones\Http\Controllers\AdministracionController;
use Modules\Capacitaciones\Http\Controllers\CapacitacionesController;
use Modules\Capacitaciones\Http\Controllers\CatalogoModulosController;
use Modules\Capacitaciones\Http\Controllers\CatalogoPuestosController;
use Modules\Capacitaciones\Models\PuestosModulosAs;

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

// Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
Route::middleware(['auth:sanctum'])->prefix('capacitaciones')->group(function () {
    Route::resource('as', CapacitacionesController::class)->names('capacitacion');
    Route::resource('adminstracion', AdministracionController::class)->names('administracion');
    Route::resource('puestos', CatalogoPuestosController::class)->names('puestos');
    Route::resource('modulos', CatalogoModulosController::class)->names('modulos');

    Route::get('/getFunciones/{nModulo}/{nSubmodulo}',[CapacitacionesController::class, 'getFunciones'])->name('capacitacion.getFunciones');
});
