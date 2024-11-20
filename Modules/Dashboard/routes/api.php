<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\EnergeticosController;
use Modules\Dashboard\Http\Controllers\AgenciasController;

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
Route::middleware([''])->group(function () {});

Route::apiResources([
    'dashboard/gasolinerias' => EnergeticosController::class,
    'dashboard/agencia-nissan' => AgenciasController::class,
]);

Route::get('energeticos-gaseras/{mes}/{anio}', [EnergeticosController::class, 'show'])->name('energeticos-gaseras.show');
Route::get('energeticos/{sub_division}', [EnergeticosController::class, 'index'])->name('subdivision.index');
Route::get('energeticos/anual/{id}', [EnergeticosController::class, 'show'])->name('energeticos_anual.index');
