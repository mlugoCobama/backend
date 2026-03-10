<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use Modules\Renault\Http\Controllers\RenaultController;
use Modules\Renault\Http\Controllers\VisorCitasController;

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
    Route::get('visor-citas/datos-ingreso/{id}', [ VisorCitasController::class, 'getDatosIngreso'])->name('visor-citas.getDatosEntrada');
    Route::post('auth/login', [LoginController::class, 'loginMobile']);
    Route::get('visor-citas/orden-servicio/{id}', [ VisorCitasController::class, 'descargarPdfOrdenServicio'])->name('visor-citas.descargarOrdenServicio');
    Route::get('visor-citas/{intercompania}/{apv}/{fechaInicial}/{fechaFinal}', [ VisorCitasController::class, 'datosFiltrados'])->name('visor-citas.datosFiltrados');
    Route::get('aps/{idAgencia}', [ VisorCitasController::class, 'getApsByAgencia'])->name('visor-citas.getApsByAgencia');
});

Route::get('storage/renault/citas_servicio/{fileName}', [VisorCitasController::class, 'getFile'])->name('visor-citas.getFile');
