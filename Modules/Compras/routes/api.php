<?php

use Illuminate\Support\Facades\Route;

use Modules\Compras\Http\Controllers\CatUnidadesMedidaController;
use Modules\Compras\Http\Controllers\ProveedoresController;
use Modules\Compras\Http\Controllers\SolicitudesCompraController;
use Modules\Compras\Http\Controllers\ExpedientesProveedoresController;
use Modules\Compras\Http\Controllers\CotizacionesController;
use Modules\Compras\Http\Controllers\OrdenesComprasController;
use Modules\Compras\Http\Controllers\DocumentosOrdenesComprasController;
use Modules\Compras\Http\Controllers\CatUnidadesController;
use Modules\Compras\Http\Controllers\SolicitudesMacroController;
use Modules\Compras\Http\Controllers\CatSistemasAutoController;
use Modules\Compras\Http\Controllers\AcuseEntregaController;
use Modules\Compras\Models\Cotizaciones;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Compras\Http\Controllers\AlmacenComprasConrollerController;
use Modules\Compras\Http\Controllers\UsuariosController;
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Http\Controllers\DetalleSolicitudController;
use Modules\Compras\Http\Controllers\CatTiposMantenimientoController;
use Modules\Compras\Http\Controllers\DispersionesDieselController;
use Modules\Compras\Models\AcuseEntrega;
use Modules\Compras\Http\Controllers\ReportesComprasController;
use Modules\Compras\Http\Controllers\SyncController;
use Modules\Compras\Http\Controllers\TokaController;

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

Route::middleware(['auth:sanctum'])->prefix('compras')->group(function () {
// Route::prefix('compras')->group(function () {
    Route::resource('CatalogoUnidades', CatUnidadesController::class);
    Route::resource('CatalogoUnidadesMedida', CatUnidadesMedidaController::class);
    Route::resource('Proveedores', ProveedoresController::class);
    Route::resource('SolicitudesCompras', SolicitudesCompraController::class);
    Route::resource('ExpedientesProveedores', ExpedientesProveedoresController::class);
    Route::resource('Cotizaciones', CotizacionesController::class);
    Route::resource('OrdenesCompras', OrdenesComprasController::class);
    Route::resource('DocumentosOrdenesCompras', DocumentosOrdenesComprasController::class);
    Route::resource('Usuarios', UsuariosController::class);
    Route::resource('SolicitudesMacro', SolicitudesMacroController::class);
    Route::resource('DetallesSolicitud', DetalleSolicitudController::class);
    Route::resource('CatalogoSistemasAuto', CatSistemasAutoController::class);
    Route::resource('CatalogoTiposMantenimiento', CatTiposMantenimientoController::class);
    Route::resource('AcuseEntrega', AcuseEntregaController::class);
    Route::resource('ReportesCompras', ReportesComprasController::class);
    Route::resource('AlmacenCompras', AlmacenComprasConrollerController::class);
    Route::resource('Toka', TokaController::class);
    Route::resource('DispersionesDiesel', DispersionesDieselController::class);
    
    Route::get('/Solicitudes/{intercompania}/{id}',[SolicitudesCompraController::class, 'index'])->name('SolicitudesCompras.solicitudes');
    Route::get('/Solicitudes/Macro/{intercompania}/{id}',[SolicitudesMacroController::class, 'index'])->name('SolicitudesMacro.solicitudes');
    Route::get('/getProveedores',[ProveedoresController::class, 'getProveedores'])->name('Proveedores.getProveedores');
    Route::get('/SolicitudCompra/{id}',[SolicitudesCompraController::class, 'getSolicitud'])->name('SolicitudesCompras.getSolicitud');
    Route::post('/devolver-solicitud-revision', [SolicitudesCompraController::class, 'devolverSolicitudEstatusAnterior'])->name('SolicitudesCompras.devolverSolicitudRevision');
    Route::post('/SolicitudesCompras/actualiza-solicitud', [SolicitudesCompraController::class, 'actualizarSolicitudCompra'])->name('SolicitudesCompras.actualizarSolicitudCompra');
    Route::post('/SolicitudesCompras/Macro/actualiza-solicitud', [SolicitudesMacroController::class, 'actualizarSolicitudCompra'])->name('SolicitudesCompras.actualizarSolicitudCompraMacro');
    Route::post('/Proveedores/{id}', [ProveedoresController::class, 'update'])->name('Proveedores.actualizar');
    Route::get('/Cotizaciones/volver-a-cotizar/{id}', [CotizacionesController::class, 'recotizar'])->name('Cotizaciones.recotizar');

    //*Ruta para enviar un email de prueba, no funciona pero va funcionar
    Route::post('/enviar-solicitud-cotizacion', [SolicitudesCompraController::class, 'enviarSolicitudCotizacion'])->name('SolicitudesCompra.enviarSolicitudCotizacion');
    Route::post('/reenviar-solicitud-cotizacion', [SolicitudesCompraController::class, 'reenviarCorreo'])->name('SolicitudesCompra.reenviarCorreo');
    Route::post('/autorizar-orden-compra', [OrdenesComprasController::class, 'autorizarOrden'])->name('OrdenesCompras.autorizarOrden');
    Route::post('/autorizar-apago-orden-compra', [OrdenesComprasController::class, 'autorizarOrdenPago'])->name('OrdenesCompras.autorizarOrdenPago');

    //Route::get('/autorizacion-solicitud-gerencia/{id}', [SolicitudesCompraController::class, 'autorizeFromEmail']);
    Route::get('/autorizacion-solicitud-gerencia/{campo}/{necesarias}/{id}', [SolicitudesCompraController::class, 'autorizeFromEmail'])->name('confirm.accion')->middleware('signed');
    //*Ruta para generar pdf
    Route::get('/consulta-datos-pdf/{id}',[OrdenesComprasController::class, 'descargarOrdenCompra'])->name('OrdenesCompras.consultaDatosPDF');
    Route::get('/preview-orden-compra-pdf/{id}',[OrdenesComprasController::class, 'previewOrdenCompra'])->name('OrdenesCompras.previewOrdenCompra');
    Route::put('/autorizar-cotizacion/{id}',[CotizacionesController::class, 'autorizarCotizacion'])->name('Cotizaciones.autorizarCotizacion');
    Route::get('/solicitar-autorizacion/{id}',[CotizacionesController::class, 'limpiarAutorizaciones'])->name('Cotizaciones.solicitaAutorizacion');

    //*Ruta para leer el XML
    Route::get('/leer-xml/{id}',[OrdenesComprasController::class, 'leerXML'])->name('OrdenesCompras.leerXML');
    Route::get('/get-data-xml/{id}', [DocumentosOrdenesComprasController::class, 'leerYProcesarXML'])->name('OrdenesCompras.leerYProcesarXML');
    
    Route::get('/getUserByEmail/{correo}', [UsuariosController::class, 'getDataUsuario'])->name('Usuarios.getDataUsuario');
    Route::get('/getUserById/{correo}', [UsuariosController::class, 'showById'])->name('Usuarios.showById');
    Route::get('/getTecnicos', [UsuariosController::class, 'getTecnicos'])->name('Usuarios.getTecnicos');
    Route::get('/getExsitencias/{tipo}', [AlmacenComprasConrollerController::class, 'getAlmacenDisponible'])->name('Almacen.getAlmacenDisponible');
    Route::get('/getMovimientos', [AlmacenComprasConrollerController::class, 'getSalidas'])->name('Almacen.getSalidas');

    //*Rutas para el catalogo de vehiculos
    Route::post('/importar-datos-seguro', [CatUnidadesController::class, 'importarDatosSeguro']);
    Route::get('/recuperar-autotanques/{intercompania}', [CatUnidadesController::class, 'getAutotanques']);
    Route::get('/recuperar-gastos-vehiculo/{id}', [CatUnidadesController::class, 'getGastoUnidad']);
    Route::get('/recuperar-comentarios-vehiculo/{id}', [CatUnidadesController::class, 'getObservaciones']);
    Route::get('/recuperar-polizas-vehiculo/{id}', [CatUnidadesController::class, 'getDatosPoliza']);
    Route::post('/autorizar-alta-vehiculo', [CatUnidadesController::class, 'autorizarVehiculo']);

    // Ruta test
    Route::post('/save-files-factura', [DocumentosOrdenesComprasController::class, 'subirDocumento'])->name('DocumentosOrdenesCompras.saveFilesFactura');
    Route::get('/get-seguimiento-solicitud/{idSolicitud}', [SolicitudesCompraController::class, 'getSeguimientoSolicitud'])->name('SolicitudesCompras.getSeguimientoSolicitud');

    Route::post('/cancelar-solicitud', [SolicitudesCompraController::class, 'cancelarSolicitud'])->name('SolicitudesCompras.cancelarSolicitud');
    Route::post('/rechazar-orden-compra', [OrdenesComprasController::class, 'rechazarOrdenCompra'])->name('OrdenesCompra.rechazarOrdenCompra');
    Route::post('/solictar-surtido-orden-compra', [OrdenesComprasController::class, 'solicitarSurtido'])->name('OrdenesCompra.solicitarSurtido');
    Route::post('/cambiar-proveedor-seleccionado', [OrdenesComprasController::class, 'cambiarProveedorSeleccionado'])->name('OrdenesCompra.cambiarProveedorSeleccionado');
    Route::get('/marcar-como-finalizada/{idOrdenCompra}', [OrdenesComprasController::class, 'markAsFinalizada'])->name('OrdenesCompra.markAsFinalizada');

    Route::get('/toka/clientes', [TokaController::class, 'getClientesToka'])->name('Toka.getClientes');

    Route::post('/CatalogoUnidades/SolicitaToka', [CatUnidadesController::class, 'saveSolicitudRecargaToka']);
    Route::post('/CatalogoUnidades/DispersaToka', [CatUnidadesController::class, 'saveRecargaToka']);
    Route::get('/CatalogoUnidades/ParqueVehicularToka/{id}', [CatUnidadesController::class, 'getParqueWithToka']);
});

Route::prefix('compras')->group(function(){
    Route::resource('SyncFacturas', SyncController::class);

    Route::post('/confirmacion-sincronizacion', [SyncController::class, 'confirmar']);
    
    Route::get('/documentos/{id}/archivos',[SyncController::class, 'obtenerArchivos']);

    Route::post('/importar-autotanques', [CatUnidadesController::class, 'actualizarDatosPV']);
    Route::get('/get-seguimiento-solicitud/{idSolicitud}', [SolicitudesCompraController::class, 'getSeguimientoSolicitud'])->name('SolicitudesCompras.getSeguimientoSolicitud');
    
    //*Ruta para mostra archivos 
    Route::get('expedientes/{id}/{file}', [ExpedientesProveedoresController::class, 'getFile'])->name('ExpedientesProveedores.getFile');
    Route::get('cotizaciones/{id}/{file}', [CotizacionesController::class, 'getFile'])->name('Cotizaciones.getFile');
    Route::get('docsOrdenCompra/{id}/{file}', [DocumentosOrdenesComprasController::class, 'getFile'])->name('DocumentosOrdenesCompras.getFile');
    Route::get('ordenesTrabajo/{id}/{file}', [SolicitudesMacroController::class, 'getFile'])->name('SolicitudesMacro.getFile');
    Route::get('acuses/{id}/{file}', [AcuseEntregaController::class, 'getFile'])->name('AcuseEntrega.getFile');
    //*Rutas descarga zip
    Route::get('/descargar-facturas/{id}',[DocumentosOrdenesComprasController::class, 'downloadFacturas'])->name('DocumentosOrdenesCompras.downloadFacturas');
    Route::get('/descargar-expediente/{id}',[ExpedientesProveedoresController::class, 'downloadExpediente'])->name('ExpedientesProveedores.downloadExpediente');
    Route::get('/download-xml/{folder}/{id}/{file}', [DocumentosOrdenesComprasController::class, 'downloadXML'])->name('DocumentosOrdenesCompras.downloadXML');

    Route::post('/subir-doc-cotizacion-destiempo', [CotizacionesController::class, 'uploadOutTimeCotizacion']);

    // Rutas de consulta de solicitudes sin autenticación
    Route::get('/SolicitudesNa/{intercompania}/{id}',[SolicitudesCompraController::class, 'index'])->name('SolicitudesCompras.solicitudesNa');
    Route::get('/SolicitudesNa/Macro/{intercompania}/{id}',[SolicitudesMacroController::class, 'index'])->name('SolicitudesMacro.solicitudesNa');
    Route::get('/download/SolicutdesCompras/{tipo}/{estatus}/{fechaInicial}/{fechaFinal}',[ReportesComprasController::class, 'downloadSolicitudes'])->name('SolicitudesMacro.downloadSolicitudes');

    //* rutas para reportes mensuales

    Route::get('/ReportesCompras/GatoMensualConcentrado/{fechaInicial}/{fechaFinal}/{tipo}', [ReportesComprasController::class, 'getGastoEmpresasConcentrado'])->name('ReportesCompras.getGastoEmpresasConcentrado');
    Route::get('/ReportesCompras/GatoMensualDetalle/{intercompania}/{fechaInicial}/{fechaFinal}/{tipo}', [ReportesComprasController::class, 'getGastoEmpresaDetalle'])->name('ReportesCompras.getGastoEmpresaDetalle');
    Route::get('/ReportesCompras/ComprasMacro/Concentrado', [ReportesComprasController::class, 'descargarConcentradoMacroGlobal'])->name('descargarConcentradoMacroGlobal');   
    Route::get('/ReportesCompras/ComprasGenerales/Concentrado', [ReportesComprasController::class, 'descargarConcentradoGeneralesGlobal'])->name('descargarConcentradoGeneralGlobal');   
    Route::get('/ReportesCompras/ComprasGeneralesTi/Concentrado', [ReportesComprasController::class, 'descargarConcentradoGeneralesTiGlobal'])->name('descargarConcentradoGeneralTiGlobal');   
// Exportar solo una empresa
    Route::get('/gasto-empresa/{empresa}/{fechaInicial}/{fechaFinal}/{tipo}', [ReportesComprasController::class, 'exportEmpresa']);
    // Exportar concentrado
    Route::get('/gastos-concentrado/{fechaInicial}/{fechaFinal}/{tipo}', [ReportesComprasController::class, 'exportConcentrado']);
    // Exportar multireporte
    Route::get('/gastos-mensuales/{fechaInicial}/{fechaFinal}/{tipo}', [ReportesComprasController::class, 'exportMulti']);

    
    // Multi-hoja
    Route::get('/reportes/gastos/concentrado', [ReportesComprasController::class, 'descargarConcentrado']);

    // Individual por empresa
    Route::get('/reportes/gastos/detalle/{intercompania}', [ReportesComprasController::class, 'descargarDetalleEmpresa']);

    Route::post('/asignar-permisos-kanban', [UsuariosController::class, 'asignar']);



    Route::get('/reportes/workflow/concentrado/{tipo}', [ReportesComprasController::class, 'getReporteEstatus']);
});

Route::post('/all-permisos', [UsuariosController::class, 'getAllPermission'])->name('consultas.permisos');
Route::post('/permisos/guardar', [UsuariosController::class, 'saveOrUpdatePermissions']);