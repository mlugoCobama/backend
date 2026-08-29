<?php

use Illuminate\Support\Facades\Route;
use Modules\Volumetricos\Http\Controllers\AcusesReporteController;
use Modules\Volumetricos\Http\Controllers\VolumetricosController;
use Modules\Volumetricos\Http\Controllers\XlxsToJsonController;

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

Route::middleware(['auth:sanctum'])->prefix('volumetricos')->group(function () {
    Route::apiResource('acuses', AcusesReporteController::class)->names('acuses');
    Route::apiResource('volumetricos', VolumetricosController::class)->names('volumetricos');
    Route::apiResource('generacion', XlxsToJsonController::class)->names('generacion');
});

Route::get('/reportes/{id}/descargar-excel', [VolumetricosController::class, 'descargarExcel']);
Route::get('/reportes/{id}/descargar-reporte', [VolumetricosController::class, 'descargar']);
Route::get('/reportes/{id}/descargar-acuse', [AcusesReporteController::class, 'descargar']);
Route::post('/volumetricos/generacion-xml', [XlxsToJsonController::class, 'descargarXml']);
