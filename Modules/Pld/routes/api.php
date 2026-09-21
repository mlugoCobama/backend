<?php

use Illuminate\Support\Facades\Route;
use Modules\Pld\Http\Controllers\PldController;

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

Route::middleware(['auth:sanctum'])->prefix('pld-autos')->group(function () {
    Route::apiResource('pld', PldController::class)->names('pld');
});
