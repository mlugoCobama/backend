<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\EnergeticosController;
use Modules\Dashboard\Http\Controllers\AgenciasController;
use Modules\Dashboard\Http\Controllers\EnergeticosGasolinerasController;
use Modules\Dashboard\Http\Controllers\AgenciasRenaultController;

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
    'dashboard/agencia-renault' => AgenciasRenaultController::class,
]);
/**--------------------------------------
 * Rutas energéticos
 -----------------------------------------*/
Route::get('energeticos-gaseras/{mes}/{anio}', [EnergeticosController::class, 'show'])->name('energeticos-gaseras.show');
Route::get('energeticos-gasolineras/{mes}/{anio}', [EnergeticosGasolinerasController::class, 'showGasolinerias'])->name('energeticos-gasolineras.showGasolinerias');
Route::get('energeticos-gasolinerias/{sub_division}/{mes}/{anio}', [EnergeticosGasolinerasController::class, 'index'])->name('energeticos-gasolineras.index');

Route::get('energeticos/{sub_division}/{mes}/{anio}/{titular}', [EnergeticosController::class, 'index'])->name('subdivision.index');
Route::get('energeticos/anual/{id}', [EnergeticosController::class, 'show'])->name('energeticos_anual.index');

Route::get('energeticos/anual-estacion/{id}/{anio}', [EnergeticosController::class, 'getAnualEstacionEnergeticos'])->name('energeticos_anual.getAnualEstacionEnergeticos');
Route::get('agencias/anual-agencias/{id}/{anio}', [AgenciasController::class, 'getAnualAgecia'])->name('agencias_anual.getAnualAgecia');
/**--------------------------------------
 * Rutas Agencias Nissan
 -----------------------------------------*/
Route::get('dashboard/agencia-nissan/{sub_division}/{mes}/{anio}', [AgenciasController::class, 'index'])->name('agencia-nissan-anual.index');
Route::get('agencia-nissan/{mes}/{anio}', [AgenciasController::class, 'showAgenciasNissan'])->name('agencia-nissan.showAgenciasNissan');
Route::get('energeticos/anual/{id}/{anio}', [EnergeticosController::class, 'showAnual'])->name('energeticos_anual.index-anul');
Route::get('show-agencia-nissan/{mes}/{anio}', [AgenciasController::class, 'getDataGridAgencia'])->name('agencia-renault.getDataGridAgencia-2');
Route::put('dashboard/edit-agencia-nissan', [AgenciasController::class, 'updateAgenciaNissan'])->name('agencia-renault.updateAgenciaNissan');

//Ruta de prueba
Route::get('antiguedad-inventarios-nissan/{mes}/{anio}', [AgenciasController::class, 'SemestreAntiguedadInventarios'])->name('agencia-nissan.SemestreAntiguedadInventarios');

/**--------------------------------------
 * Rutas Agencias Renault
 -----------------------------------------*/
Route::get('dashboard/agencia-renault/{sub_division}/{mes}/{anio}', [AgenciasRenaultController::class, 'index'])->name('agencia-renault-anual.index');
Route::get('show-agencia-renault/{mes}/{anio}', [AgenciasRenaultController::class, 'getDataGridAgencia'])->name('agencia-renault.getDataGridAgencia');
Route::put('dashboard/edit-agencia-renault', [AgenciasRenaultController::class, 'updateAgenciaRenault'])->name('agencia-renault.updateAgenciaRenault');