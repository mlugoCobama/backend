<?php

use Illuminate\Support\Facades\Route;
use Modules\Ucoip\Http\Controllers\AsignacionRecursoController;
use Modules\Ucoip\Http\Controllers\AsignacionSistemaController;
use Modules\Ucoip\Http\Controllers\AsignacionSoftwareController;
use Modules\Ucoip\Http\Controllers\AsignacionTokensController;
use Modules\Ucoip\Http\Controllers\CatAreasController;
use Modules\Ucoip\Http\Controllers\CatHardwareController;
use Modules\Ucoip\Http\Controllers\CatServicioController;
use Modules\Ucoip\Http\Controllers\CatSoftwareController;
use Modules\Ucoip\Http\Controllers\EmpresasController;
use Modules\Ucoip\Http\Controllers\HardwareController;
use Modules\Ucoip\Http\Controllers\HardwareInfraController;
use Modules\Ucoip\Http\Controllers\ModulosController;
use Modules\Ucoip\Http\Controllers\PermisosController;
use Modules\Ucoip\Http\Controllers\ResguardosController;
use Modules\Ucoip\Http\Controllers\ServiciosController;
use Modules\Ucoip\Http\Controllers\TokensAgenciaController;
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

Route::middleware(['auth:sanctum'])->prefix('ucoip')->group(function () {
    Route::apiResource('ucoip', UcoipController::class)->names('ucoip');
    Route::apiResource('areas', CatAreasController::class)->names('areas');
    Route::apiResource('hardware', HardwareController::class)->names('hardware');
    Route::apiResource('hardware-infra', HardwareInfraController::class)->names('hardware-infra');
    Route::apiResource('cat-hardware', CatHardwareController::class)->names('cat-hardware');
    Route::apiResource('cat-software', CatSoftwareController::class)->names('cat-software');
    Route::apiResource('modulos', ModulosController::class)->names('modulos-sistema');
    Route::apiResource('permisos', PermisosController::class)->names('permisos');
    Route::apiResource('cat-servicios', CatServicioController::class)->names('cat-servicios');
    Route::apiResource('servicios', ServiciosController::class)->names('servicios');
    Route::apiResource('resguardos', ResguardosController::class)->names('resguardos');
    Route::apiResource('tokens-agencias', TokensAgenciaController::class)->names('tokens-agencias');
    Route::apiResource('empresas', EmpresasController::class)->names('empresas');
    Route::apiResource('tokens-ucoip', AsignacionTokensController::class)->names('tokens-ucoip');
    Route::apiResource('sistema-ucoip', AsignacionSistemaController::class)->names('sistemas-ucoip');
    Route::apiResource('recurso-ucoip', AsignacionRecursoController::class)->names('recurso-ucoip');
    Route::apiResource('software-ucoip', AsignacionSoftwareController::class)->names('software-ucoip');


    Route::get('/hardware/catalogo/disponible/{idEmpresa}',[CatHardwareController::class, 'getCatalogoDisponible'])->name('cat-hardware.disponible');
    Route::get('/software/catalogo/disponible/{idEmpresa}',[CatSoftwareController::class, 'getCatalogoDisponible'])->name('cat-software.disponible');
    Route::get('/sistema-ucoip/password/{id}',[AsignacionSistemaController::class, 'getPassword'])->name('sistemas-ucoip.getPassword');
    Route::get('/tokens-ucoip/password/{id}/{campo}',[AsignacionTokensController::class, 'getPassword'])->name('tokens-ucoip-ucoip.getPassword');
    Route::get('/ucoip/password/{id}',[UcoipController::class, 'getPassword'])->name('ucoip.getPassword');
    
    Route::get ('/catalogos', [UcoipController::class, 'getCatalogosUcoip'])->name('ucoip.catalogos');
    Route::get ('/cat/hardware/infra', [CatHardwareController::class, 'getCatInfra'])->name('cat-hardware.getCatInfra');

});
