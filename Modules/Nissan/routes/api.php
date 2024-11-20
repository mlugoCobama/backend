<?php

use Illuminate\Support\Facades\Route;
use Modules\Nissan\Http\Controllers\CompraSeminuevosController;
use Modules\Nissan\Http\Controllers\CompraSeminuevosPDFController;
use Modules\Nissan\Http\Controllers\NissanController;

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
//Route::apiResource('nissan', NissanController::class)->names('nissan');

//Route::middleware([''])->prefix('nissan')->group(function () {
Route::prefix('nissan')->group(function () {
    Route::apiResource('compra-seminuevos', CompraSeminuevosController::class)->names('compra-seminuevos');
    Route::apiResource('compra-seminuevos-pdf', CompraSeminuevosPDFController::class)->names('compra-seminuevos-pdf');
});
