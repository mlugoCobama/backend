<?php

use Illuminate\Support\Facades\Route;
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

Route::middleware([])->prefix('v1')->group(function () {
    Route::apiResource('ucoip', UcoipController::class)->names('ucoip');
});
