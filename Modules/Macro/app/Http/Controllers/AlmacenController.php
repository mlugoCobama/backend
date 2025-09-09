<?php

namespace Modules\Macro\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Transformers\DetalleSolicitudCompraResource;
use Modules\Macro\Models\Almacen;
use Modules\Macro\Models\Salida;
use Modules\Macro\Transformers\AlmacenResource;
use Modules\Macro\Transformers\DetalleAlmacenResource;

class AlmacenController extends Controller
{
    /**
     * Recupera un listado general del almacén (virtual) de macrotaller 
     */
    public function index()
    {
        $almacen = (DB::connection('dashboard')->select('call SistemaTickets.SP_GetAlmacenGeneral'));

        return response()->json([
            'status' => 'success',
            'data' => AlmacenResource::collection($almacen),
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('macro::create');
    }

    /**
     * Almacena los detalles de almacén
     */
    public function store(Request $request)
    {

        $data = $request->all();
        try {

            $this->storeDetalleAlmacen($data);

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'message' => 'Entrada realizada con éxito'

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrio un error al guardar los datos',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Almacena los detalles de salidas de almacén
     */
    public function salida(Request $request)
    {
        $data = $request->all();

        $salida =  $request->salida;
        $detallesSalida = $request->detalles;

        try {

            $this->storeSalida($detallesSalida, $salida);
            return response()->json([
                'status' => 'success',
                'data' => $data,
                'message' => 'Entrada realizada con éxito'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrio un error al guardar los datos',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Recupera registros de detalles pendiente por entrar a almacén
     */
    public function show($id)
    {
        $detalles = DetalleSolicitudCompraResource::collection(
            (DetalleSolicitud::where('solicitudes_compra_id', $id)
                ->confirmadas()
                ->pendientes()
                ->get())
        );

        return response()->json([
            'status' => 'success',
            'data' => $detalles,
            'message' => 'Datos obtenidos correctamente'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('macro::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Recupera las compras disponibles (Autorizadas/Entregadas) por intercompania
     * @param int $intercompania
     */
    public function getCompra($intercompania)
    {
        // $tipo_dato
        $datos = (DB::connection('dashboard')->select('call SistemaTickets.SP_GetComprasMacroRecibidas(' . $intercompania . ')'));

        return response()->json([
            'status' => 'success',
            'data' => $datos,
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    /**
     * Recupera las compras disponibles en almacén por intercompania
     * @param int $intercompania
     */
    public function getComprasMacroAlmacenadas($intercompania)
    {
        $datos = (DB::connection('dashboard')->select('call SistemaTickets.SP_GetComprasMacroAlmacenadas(' . $intercompania . ')'));

        return response()->json([
            'status' => 'success',
            'data' => $datos,
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    /**
     * Recupera las compras disponibles en almacén por intercompania
     * @param int $intercompania
     * @param int $tipo tipo de parametro por el que se busca
     */
    public function showDetallesOrdenTrabajo($tipo, $idSolicitud)
    {
        if ($tipo == "nro_economico") {
            $datos =  DetalleAlmacenResource::collection(DB::connection('dashboard')->select('call SistemaTickets.SP_GetExistenciaAutotanque(' . $idSolicitud . ')'));
        } else {
            $datos =  DetalleAlmacenResource::collection(DB::connection('dashboard')->select('call SistemaTickets.SP_GetExistenciaSolicitudCompra(' . $idSolicitud . ')'));
        }

        return response()->json([
            'status' => 'success',
            'data' => $datos,
            'message' => 'Datos obtenidos correctamente'
        ]);
    }

    /**
     * Almacena los detalles en el almacén
     * @param $data almacena los detalles en almacén 
     */
    public function storeDetalleAlmacen($data)
    {
        foreach ($data as $item) {
            $dato = new Almacen();
            $dato->cant_recibida = $item["recibidos"];
            $dato->existencia = $item["recibidos"];
            $dato->fecha_entrada = date('Y-m-d H:i:s') ?? now();
            $dato->observaciones = $item["comentario"];
            $dato->com_detalle_solicitud_id = $item["id"];
            $dato->save();

            $this->updateStatusAlmacen($item["id"], 1);
        }
    }

    /**
     *  Actualiza el estatus del detalle
     * @param int $idDetSol id de detalle de solicitud
     * @param int $status status al cual se actualiza
     */
    public function updateStatusAlmacen($idDetSol, $status)
    {
        DetalleSolicitud::where('id', $idDetSol)->update([
            'estatus_almacen' =>  $status,
        ]);
    }

    /**
     * Almacena los detalles de salida
     * @param $data detalles de salida
     * @param $salida datos del salida, empresa, técnico
     */
    public function storeSalida($data, $salida)
    {

        $tecnico = $salida['tecnico'];

        foreach ($data as $item) {
            $dSolicitud = $item['idDs'];
            $idAlmacen = $item["id"];
            $cantSalida = $item["recibidos"];
            $existencia = $item["cantidad"];

            $dato = new Salida();
            $dato->fecha = date('Y-m-d H:i:s') ?? now();
            $dato->mcr_almacen_id = $idAlmacen;
            $dato->mcr_tecnicos_id = $tecnico;
            $dato->cantidad = $cantSalida;
            $dato->observaciones = $item["comentario"];
            $dato->save();

            $this->updateExistencia($idAlmacen, $existencia, $cantSalida, $dSolicitud);
        }
    }

    /**
     * Actualiza la existencia de los datos detalles de almacén
     * @param int $idAlmacen id de detalle de almacen 
     * @param $existencia 
     * @param $cantSalida id de detalle de solicitud
     * @param $id_ds id de detalle de solicitud
     */
    public function updateExistencia($idAlmacen, $existencia, $cantSalida, $id_ds)
    {
        $nuevaExt =  $existencia - $cantSalida;
        Almacen::where('id', $idAlmacen)->update([
            'existencia' =>  $nuevaExt,
        ]);

        if ($nuevaExt == 0) {
            $this->updateStatusAlmacen($id_ds, 2);
        }
    }
}
