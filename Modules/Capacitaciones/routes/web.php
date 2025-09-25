<?php

use Illuminate\Support\Facades\Route;
use Modules\Capacitaciones\Http\Controllers\CapacitacionesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {
    Route::resource('capacitaciones', CapacitacionesController::class)->names('capacitaciones');
});
