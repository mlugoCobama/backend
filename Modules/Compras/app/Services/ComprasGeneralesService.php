<?php

namespace Modules\Compras\Services;

use App\Enums\EstatusOrdenCompra;
use App\Enums\EstatusSolicitud;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Compras\Models\CatalogoPrecios;
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\DetallesCotizacion;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Models\OrdenCompra;
use Modules\Compras\Models\Proveedores;
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Notifications\AutorizacionEmail;
use Modules\Compras\Transformers\EmailAutorizarSolicitudResource;
use Normalizer;

class ComprasGeneralesService
{

    public function __construct(
        protected CotizacionesService $cotService
    ) {}


    /**
     * ----------------------------------------------------------------------
     * Queries
     * ----------------------------------------------------------------------
     */
    /**
     * Recupera las solicitudes de compra con el folio y total de la orden de compra
     *
     * @param mixed $intercompania Numero de intercompania de la empresa
     * @param mixed $autoga Autorizacion de gerencia administrativa (0 o 1)
     * @param mixed $autogg Autorizacion de gerencia (0 o 1)
     * @param mixed $tipoSolicitud tipo de solicitud (1= compras, 2 = rt, null = ambas)
     * @param mixed $idUserObjetivo usuario objetivo (null = no aplica el filtro)
     */
    public function getSolicitudesCompras($intercompania, $autoga, $autogg, $tipoSolicitud, $idUserObjetivo, $tipoUsuario, $anio, $mes)
    {
        $mesFormateado =  sprintf("%02d", $mes);
        return DB::select('CALL SP_GetSolicitudesComprasMesAnio(?, ?, ?, ?, ?, ?, ?, ?)', [$intercompania, $autoga, $autogg, $tipoSolicitud, $idUserObjetivo, $tipoUsuario, $anio, $mesFormateado]);
    }

    /**
     * ----------------------------------------------------------------------
     * Utilities
     * ----------------------------------------------------------------------
     */
    /**
     * Genera un nuevo folio consecutivo en base al ultimo folio
     */
    public function generarFolioSc($tipo)
    {
        // $tipo = strval(1);
        $sufijos = ['1' => "SC-", '3' => "SC-TI-", '4' => "SC-A-"];

        $ultimaOrden = SolicitudesCompra::active()
            ->where('tipo', $tipo)
            ->where('folio', 'LIKE', '%' . $sufijos[$tipo] . '%')
            ->orderBy('id', 'desc')
            // ->active()
            ->first('folio');
        if ($ultimaOrden) {
            $ultimoFolio = $ultimaOrden->folio;
            $numero = intval(substr($ultimoFolio, strlen($sufijos[$tipo]))) + 1;
        } else {
            $numero = 1;
        }
        $nuevoFolio = $sufijos[$tipo] . str_pad($numero, 5, '0', STR_PAD_LEFT);
        return  $nuevoFolio;
    }


    function sanitizeMeasurementText($text)
    {

        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        if (class_exists('Normalizer')) {
            $text = normalizer_normalize($text, Normalizer::FORM_C);
        }

        $replacements = [
            "″" => '"',
            "”" => '"',
            "“" => '"',
            "′" => "'",
            "’" => "'",
            "‘" => "'",
            "–" => "-",
            "—" => "-",
            "×" => "x",
            "⁄" => "/",
        ];

        $text = strtr($text, $replacements);
        $text = preg_replace('/[^\P{C}\n]+/u', '', $text);
        return trim($text);
    }

    /** ********************************************************************
     *Primero genero una solicitud de compra
     *Después almaceno los detalles de la solicitud
     **********************************************************************/
    public  function storeSolicitudCompra($data)
    {

        $usuariosRT = config('compras.users.rt', []);
        $isInfra = in_array($data["usuario_solicita"], $usuariosRT);

        $usuariosSopGas = config('compras.users.sop_gas', []);
        $isSopGas = in_array($data["usuario_solicita"], $usuariosSopGas);

        $usuariosComprasAutos = config('compras.users.autos', []);
        $isComprasAutos = in_array($data["usuario_solicita"], $usuariosComprasAutos);

        $isAutos = $data["isAgencia"];
        $tipoSolicitud = match (true) {
            $isInfra => 3,
            $isAutos => 4,
            $isComprasAutos => 4,
            default => 1
        };

        $dataSolicitud                      = new SolicitudesCompra();
        $dataSolicitud->folio               = $this->generarFolioSc($tipoSolicitud);
        $dataSolicitud->usuario_solicita    = $data["usuario_solicita"];
        $dataSolicitud->empresa             = $data["empresa"];
        $dataSolicitud->usuario_destino     = $data["usuario_destino"];
        $dataSolicitud->motivo              = $data["motivo"];
        $dataSolicitud->fecha               = now();
        $dataSolicitud->c_c                 = $data["c_c"];
        $dataSolicitud->requiere_anticipo   = ($data["requiere_anticipo"] === "true") ? 1 : 0;
        $dataSolicitud->tipo                = $tipoSolicitud;
        $dataSolicitud->com_cat_sistemas_auto_id         = $data["sistema"] ?? null;
        if ($isInfra) {
            $dataSolicitud->auto_admin                       = $isSopGas  ? 0 : 1;
            $dataSolicitud->auto_gg                          = $isSopGas  ? 0 : 1;
            $dataSolicitud->auto_macro                       = 1;
            $dataSolicitud->estatus                          = $isSopGas ? 1 : 2;
            $dataSolicitud->com_cat_tipos_mantenimiento_id   = $data["tipoMantenimiento"];
        }

        $dataSolicitud->save();
        return [
            'id' => $dataSolicitud->id,
            'destino' => $data["usuario_destino"]
        ];
    }

    /** ***************************************************************************
     * Amacena los detalles de la solicitud
     *****************************************************************************/
    public function storeDetalleSolicitudCompra($detalles, $idSolicitud, $files, $destino)
    {
        $idsProductos = [];
        foreach ($detalles as $index => $detalle) {
            $detalleSolicitud                           = new DetalleSolicitud();
            $detalleSolicitud->com_productos_id         = $detalle['id_producto'];
            $detalleSolicitud->cantidad                 = $detalle["cantidad"];
            $detalleSolicitud->descripcion              = $this->sanitizeMeasurementText($detalle["descripcion"]);
            $detalleSolicitud->observaciones            = $this->sanitizeMeasurementText($detalle["observaciones"]);
            $detalleSolicitud->cat_unidades_medida_id   = $detalle["cat_unidades_medida_id"];
            $detalleSolicitud->recuperable              = $detalle["recuperar_costo"];

            $fileKey = "img_referencia_" . $index;
            if (isset($files[$fileKey]) && $files[$fileKey]->isValid()) {
                if (in_array($files[$fileKey]->extension(), ['jpg', 'jpeg', 'png'])) {
                    $path                               = $files[$fileKey]->store('referencias', 'public');
                    $detalleSolicitud->img_referencia   = $path;
                } else {
                    throw new \Exception("El archivo debe ser una imagen JPG o PNG");
                }
            }

            $detalleSolicitud->solicitudes_compra_id = $idSolicitud;
            $detalleSolicitud->save();

            if (isset($detalle['id_producto'])) {
                $idsProductos[$detalle['id_producto']] =  $detalleSolicitud->id;
            }
        }

        if (!empty($idsProductos)) {
            $this->generarCotizacionesAutomaticas(array_keys($idsProductos), $idsProductos, $idSolicitud);
        }
    }

    /**
     * Método para procesar y generar las cotizaciones por proveedor
     */
    private function generarCotizacionesAutomaticas(array $listIdProductos, array $cantidadesPorProducto, $idSolicitud)
    {
        $preciosProveedores = CatalogoPrecios::
            //   with('proveedor')->
            whereIn('com_productos_id', $listIdProductos)
            ->get();

        if ($preciosProveedores->isEmpty()) {
            return;
        }

        $cotizacion                         = new Cotizaciones();
        $cotizacion->folio                  = $this->cotService->generarFolioCo();
        $cotizacion->fecha                  = now();
        $cotizacion->consideraciones        = '';
        $cotizacion->solicitudes_compra_id  = $idSolicitud;
        $cotizacion->save();

        $productosPorProveedor = $preciosProveedores->groupBy('com_proveedores_id');

        foreach ($productosPorProveedor as $idProveedor => $catalogoItems) {

            $proveedor = Proveedores::where('id', $idProveedor)->first();

            $cotProv = CotizacionesProveedores::create([
                'proveedores_id'    => $idProveedor,
                'cotizaciones_id'   => $cotizacion->id,
                'ruta'              => $proveedor->catalogo_precios,

            ]);

            foreach ($catalogoItems as $item) {
                $productoId = $item->com_productos_id;
                $idDtalle   = $cantidadesPorProducto[$productoId] ?? 1;

                DetallesCotizacion::create([
                    'cotizaciones_proveedores_proveedores_id' => $cotProv->id,
                    'detalle_solicitud_id' => $idDtalle,
                    'importe_unitario' => $item->precio
                ]);
            }
        }

        $solicitud = SolicitudesCompra::find($idSolicitud);
        if($solicitud){
            $solicitud->estatus = EstatusSolicitud::EN_COTIZACION;
            $solicitud->save();
        }
    }

    public function devolverSolicitudEstatusAnterior($request)
    {
        $solicitud = SolicitudesCompra::find($request->id);

        if (!$solicitud) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Solicitud no encontrada',
                'data'    => null
            ]);
        }
        $estatusActual = $solicitud->estatus;
        if ($solicitud->tipo == 2 || $solicitud->tipo == 1) {
            $solicitud->auto_gg     = 0;
            $solicitud->auto_admin  = 0;
        }

        if ($solicitud->tipo == 2) {
            $solicitud->auto_macro = 0;
        }

        $solicitud->motivo_revision = $request->motivo;
        $solicitud->estatus = $solicitud->estatus > 1 ? $estatusActual - 1 : $estatusActual;

        $solicitud->save();

        NotificationHelper::sendNotificationEstatusChange($solicitud->id, "En espera de autorización - \nRevisar y modificar la solicitud - \nOBSERVACIONES: " . $solicitud->motivo_revision);
        return $solicitud;
    }


    /**
     * Actualiza una solicitud de compra existente
     */
    public function updateSolicitudCompra($data, $idSolicitud)
    {
        $usuariosRT = config('compras.users.rt', []);
        $isInfra = in_array($data["usuario_solicita"], $usuariosRT);
        $usuariosSopGas = config('compras.users.sop_gas', []);
        $isSopGas = in_array($data["usuario_solicita"], $usuariosSopGas);



        $dataSolicitud = SolicitudesCompra::findOrFail($idSolicitud);

        // Actualizar campos
        $dataSolicitud->usuario_solicita = $data["usuario_solicita"] ?? $dataSolicitud->usuario_solicita;
        $dataSolicitud->empresa = $data["empresa"] ?? $dataSolicitud->empresa;
        $dataSolicitud->usuario_destino = $data["usuario_destino"];
        $dataSolicitud->motivo = $data["motivo"];
        $dataSolicitud->c_c = $data["c_c"];
        $dataSolicitud->requiere_anticipo = ($data["requiere_anticipo"] === "true") ? 1 : 0;

        if ($isInfra) {
            $dataSolicitud->tipo = 3;
            $dataSolicitud->auto_admin = $isSopGas ? 0 : 1;
            $dataSolicitud->auto_gg = $isSopGas ? 0 : 1;
            $dataSolicitud->auto_macro = 1;
            $dataSolicitud->estatus = $isSopGas ? 1 : 2;
            $dataSolicitud->com_cat_sistemas_auto_id = $data["sistema"];
            $dataSolicitud->com_cat_tipos_mantenimiento_id = $data["tipoMantenimiento"];
        }
        $dataSolicitud->motivo_revision = null;
        $dataSolicitud->save();

        return [
            'id' => $dataSolicitud->id,
            'destino' => $data["usuario_destino"]
        ];
    }

    /**
     * Actualiza los detalles de la solicitud de compra
     * Elimina los que no vienen en el request y actualiza/crea los demás
     */
    public function updateDetalleSolicitudCompra($detalles, $idSolicitud, $files, $destino)
    {
        $detallesIds = collect($detalles)->pluck('id')->filter()->toArray();

        // Eliminar los detalles que ya no vienen en el request junto con sus imágenes
        $detallesAEliminar = DetalleSolicitud::where('solicitudes_compra_id', $idSolicitud)
            ->when(!empty($detallesIds), fn($q) => $q->whereNotIn('id', $detallesIds))
            ->get();

        foreach ($detallesAEliminar as $detalle) {
            if ($detalle->img_referencia && Storage::disk('public')->exists($detalle->img_referencia)) {
                Storage::disk('public')->delete($detalle->img_referencia);
            }
            $detalle->delete();
        }

        // Iterar para actualizar o crear...
        foreach ($detalles as $index => $detalle) {
            $detalleSolicitud = DetalleSolicitud::updateOrCreate(
                [
                    'id' => $detalle['id'] ?? null,
                    'solicitudes_compra_id' => $idSolicitud
                ],
                [
                    'cantidad' => $detalle["cantidad"],
                    'descripcion' => $this->sanitizeMeasurementText($detalle["descripcion"]),
                    'observaciones' => $this->sanitizeMeasurementText($detalle["observaciones"]),
                    'cat_unidades_medida_id' => $detalle["cat_unidades_medida_id"],
                    'recuperable' => $detalle["recuperar_costo"] ?? $detalle["recuperable"] ?? 0,
                    'confirmado' => $detalle["confirmado"] ?? ($detalleSolicitud->confirmado ?? null),
                ]
            );

            // Manejo de archivos...
            $fileKey = "img_referencia_" . $index;
            if (isset($files[$fileKey]) && $files[$fileKey]->isValid()) {
                if (!in_array($files[$fileKey]->extension(), ['jpg', 'jpeg', 'png'])) {
                    throw new \Exception("El archivo debe ser una imagen JPG o PNG");
                }

                if ($detalleSolicitud->img_referencia && Storage::disk('public')->exists($detalleSolicitud->img_referencia)) {
                    Storage::disk('public')->delete($detalleSolicitud->img_referencia);
                }

                $detalleSolicitud->img_referencia = $files[$fileKey]->store('referencias', 'public');
                $detalleSolicitud->save();
            }
        }
    }

    /**
     * Envía el correo de solicitud de autorización
     * agrega los parámetros para la url del botón 'Autorizo'
     */
    public function sendSolicitudAutorizacion($id, $correos)
    {
        $data = (new EmailAutorizarSolicitudResource(SolicitudesCompra::findOrFail($id)))->resolve();
        $data['autoNecesarias'] = $correos['autoNecesarias'];
        foreach ($correos['data'] as $correo) {
            $email = $correo['name'];
            $data['campo'] = $correo['campo'];
            Notification::route('mail', $email)
                ->notify(new AutorizacionEmail($data));
        }
    }

    /**
     * Recupera los datos de los gerente de las empresas en base a
     * su numero de intercompania
     */
    private function getGerente($intercompania)
    {
        $interAgencias = array_flip([7064, 7063, 7062, 7061]);
        $isRenault = isset($interAgencias[$intercompania]);
        if ($isRenault) {
            $intercompania = 7064;
        }
        $data = DB::connection('intranet')->select('call SOPORTEZM.SP_GetGereneciaEmpresas(?)', [$intercompania]);
        $subCadena = 'gerencia';
        $autoNecesarias  = count($data);
        if ($autoNecesarias > 0) {
            foreach ($data as $dato) {
                $isGerente = strpos($dato->name, $subCadena);
                if ($isGerente !== false) {
                    $dato->isGerente = true;
                    $dato->campo = 'gg';
                } else {
                    $dato->isGerente = $isGerente;
                    $dato->campo = 'admin';
                }
            }
            return [
                'data' =>  $data,
                'autoNecesarias' => $autoNecesarias
            ];
        }
    }

    /**
     * Verifica si ya esta autorizado por gerencias
     * y actualiza el estatus a siguiente
     */
    public function validarAutorizacion($id)
    {
        $solicitud = SolicitudesCompra::findOrFail($id);
        if ($solicitud->auto_admin === 1 && $solicitud->auto_gg === 1 && $solicitud->tipo === 1) {
            $solicitud->estatus = EstatusSolicitud::SOLICITADO;
            NotificationHelper::sendNotificationEstatusChange($solicitud->id, 'Solicitado');
            $solicitud->save();
        }

        if (
            $solicitud->auto_admin === 1 && $solicitud->auto_gg
            && $solicitud->auto_macro === 1 && $solicitud->tipo === 2
        ) {
            $solicitud->estatus = EstatusSolicitud::SOLICITADO;
            NotificationHelper::sendNotificationEstatusChange($solicitud->id, 'Solicitado');
            $solicitud->save();
        }
    }

    public function cancelarSolicitud($data)
    {
        $solicitudCompra = SolicitudesCompra::find($data['id'] ?? $data[0]);

        if (!$solicitudCompra) {
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas actualizar no existe',
                'data' => []
            ]);
        }

        $solicitudCompra->estatus = EstatusSolicitud::CANCELADA;
        $solicitudCompra->razon_cancelacion = $data['razonCancelacion'] ?? null;
        $solicitudCompra->save();

        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $solicitudCompra->id)->first();
        if ($cotizacion) {
            $ordenCompra = OrdenCompra::where('cotizaciones_id', $cotizacion->id)->first();
            if ($ordenCompra) {
                $ordenCompra->estatus = EstatusOrdenCompra::CANCELADA;
                $ordenCompra->razon_cancelacion = $data['razonCancelacion'] ?? null;
                $ordenCompra->save();
            }
        }

        NotificationHelper::sendNotificationEstatusChange($solicitudCompra->id, 'Cancelada: Razón - ' . $solicitudCompra->razon_cancelacion);
    }
}
