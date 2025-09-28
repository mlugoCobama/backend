<?php

use Illuminate\Support\Facades\Route;
use Modules\Ucoip\Http\Controllers\CatHardwareController;
use Modules\Ucoip\Http\Controllers\HardwareController;
use Modules\Ucoip\Http\Controllers\ModulosController;
use Modules\Ucoip\Http\Controllers\PermisosController;
use Modules\Ucoip\Http\Controllers\UcoipController;

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

Route::middleware([])->prefix('ucoip')->group(function () {
    Route::apiResource('ucoip', UcoipController::class)->names('ucoip');
    Route::apiResource('hardware', HardwareController::class)->names('hardware');
    Route::apiResource('cat-hardware', CatHardwareController::class)->names('cat-hardware');
    Route::apiResource('modulos', ModulosController::class)->names('modulos-sistema');
    Route::apiResource('permisos', PermisosController::class)->names('permisos');
});
