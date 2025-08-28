<?php

use Illuminate\Support\Facades\Route;
use Modules\Macro\Http\Controllers\MacroController;
use Modules\Macro\Http\Controllers\TecnicoController;

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
    prefix('macrotaller')->group(function () {
    Route::apiResource('macro', MacroController::class)->names('macro');
    Route::apiResource('tecnico', TecnicoController::class)->names('tecnico');
});
