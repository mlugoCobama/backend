<?php

use Illuminate\Support\Facades\Route;
use Modules\Compras\Http\Controllers\ComprasController;
use Modules\Compras\Http\Controllers\OrdenesComprasController;
use Modules\Compras\Http\Controllers\SyncController;

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
    Route::resource('compras', ComprasController::class)->names('compras');
    
});

Route::get('/archivosXML/{archivoId}',[SyncController::class, 'streamArchivoXML'])->name('archivosXML.stream');
Route::get('/archivosPDF/{archivoId}',[SyncController::class, 'streamArchivoPDF'])->name('archivosPDF.stream');
Route::get('/orden-compra/{idSolicitudCompra}/pdf', [SyncController::class, 'streamOrdenCompra'])->name('ordenCompra.stream');

