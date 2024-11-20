<?php

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
});
