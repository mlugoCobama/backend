<?php

use App\Http\Controllers\AgenciasController;
use App\Http\Controllers\ApiCodigoPostalController;
use App\Http\Controllers\BalanzasController;
use App\Http\Controllers\CatEmpresasController;
use App\Http\Controllers\DatosAcumuladosController;
use App\Http\Controllers\EnergeticosController;
use App\Http\Controllers\IngresosGastosController;
use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/auth/login', [LoginController::class, 'login']);
Route::get('/auth/login', [LoginController::class, 'loginValidate'])->name('login');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group( function () {
});

Route::apiResources([
    'data' => DatosAcumuladosController::class,
    'balanza' => BalanzasController::class,
    'ingresos-gastos' => IngresosGastosController::class,
    'cat-empresas' => CatEmpresasController::class,
]);


Route::get('balanza/{empresa_id}/{mes}/{anio}', [BalanzasController::class, 'show'])->name('balanza.mostrar');
Route::get('ingresos-gastos/{empresa_id}/{mes}/{anio}', [IngresosGastosController::class, 'show'])->name('ingresos-gastos.mostrar');

Route::get('search-cp/{cp}', [ApiCodigoPostalController::class, 'index'])->name('apiCP.index');
