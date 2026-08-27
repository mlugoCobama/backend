<?php

namespace Modules\Compras\Http\Controllers;

use App\Exports\GastosVehiculoExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Compras\Models\ComRecargasVehiculos;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Models\ObservacionVehiculo;
use Modules\Compras\Services\DispersionDiesel;
use Modules\Compras\Services\ParqueVehicularService;
use Modules\Compras\Transformers\ObservacionesVehiculoResource;
use Modules\Compras\Transformers\PolizasSeguroResource;
use Modules\Compras\Transformers\RecargaTokaResource;
use Modules\Compras\Transformers\VehiculosTanquesResources;
use Modules\Macro\Models\SeguroVehiculo;

class CatUnidadesController extends Controller
{

    protected $pvService;
    protected $dispersionDiesel;

    public function __construct(ParqueVehicularService $pvService, DispersionDiesel $dispersionDiesel)
    {
        $this->pvService = $pvService;
        $this->dispersionDiesel = $dispersionDiesel;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->dispersionDiesel->notificarDispersion('s' ,7);
        // $data = VehiculosTanquesResources::collection(DB::select("call SistemaTickets.SP_GetDataAutotanques()"));
        // $data =  $this->pvService->sincronizarGpsVehiculos();
        return response()->json(
            [
                'status' => 'success',
                'data' => $data,
                'message' => "Consulta generada correctamente"
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('compras::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->id;

        try {
            $vehiculo = DatosVehiculo::where( 'no_serie' , $data['datosVehiculo']['no_serie'])->first();
            if($vehiculo){
                return response()->json([
                    'status' => 'error',
                    'message' => 'El vehiculo ya existe dentro del parque vehicular con el numero economico '. $vehiculo->nro_economico,
                    'data' => []
                ]);
            }

            $datosVehiculo = $data['datosVehiculo'];
            $datosTanque = $data['datosTanque'] ?? null;
            $datosPoliza = $data['datosPoliza'] ?? null;
            $numIntercompania = $data['intercompania'];

            DB::beginTransaction();
            if (isset($data['datosVehiculo'])) {
                $vehiculo = $this->pvService->storeVehiculo($datosVehiculo, $numIntercompania, $userId);
                $idToka = $data['datosVehiculo']['num_tarjeta_toka'] === 'null' ? null : $data['datosVehiculo']['num_tarjeta_toka'];
                $idTag = $data['datosVehiculo']['num_tag'] === 'null' ? null : $data['datosVehiculo']['num_tag'];
                 $this->pvService->asignarToka($idToka,  $vehiculo);
                 $this->pvService->asignarTag($idTag, $vehiculo);
                if (
                    $datosVehiculo['tipo_vehiculo'] == "1"
                    || $datosVehiculo['tipo_vehiculo'] == "3"
                    || $datosVehiculo['tipo_combustible'] == "4"
                ) {
                    if (isset($data['datosTanque'])) {
                        $this->pvService->storeTanque($vehiculo, $datosTanque, $numIntercompania);
                    }
                }

                if ($data['hasDatosSeguro']) {
                    if (isset($data['datosPoliza'])) {
                        $this->pvService->storeDatosPoliza($datosPoliza, $vehiculo);
                    }
                }
            }

            DB::commit();

            return response()->json([
                "status" => "success",
                "message" => "Datos guardados correctamente",
                "data" => $data
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar los datos',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show the specified resource.
     *
     * @param $id int numero intercompania de la empresa
     */
    public function show(Request $request, $id, $tipo)
    {
        $usuario = $request->user()->id;

        $allAcces = [2247, 2395];

        if (in_array($usuario, $allAcces)) {
            $limite =  2;
        } else {
            $limite =  1;
        }

        $data = VehiculosTanquesResources::collection(DB::select("call SistemaTickets.SP_GetDataAutotanques(?,?,?)", [$id, $limite, $tipo]));

        return response()->json([
            "status" => "Success",
            "data" => $data,
            "message" => "Datos recuperados correctamente",
            'usuario' => $request->user()->id
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $userId = $request->user()->id;

        try {
            $datosVehiculo = $data['datosVehiculo'];
            $datosTanque = $data['datosTanque'] ??  null;
            $datosPoliza = $data['datosPoliza'] ?? null;

            $numIntercompania = $id;


            $id_vehiculo = $datosVehiculo['id'];
            $id_tanque = $datosTanque['id'];
            $id_poliza = $datosPoliza['idSeguro'];
            DB::beginTransaction();


            $this->pvService->updateVehiculo($datosVehiculo, $id_vehiculo, $userId);

            $idToka = $data['datosVehiculo']['num_tarjeta_toka'] == 'null' ? null : $data['datosVehiculo']['num_tarjeta_toka'];

            $this->pvService->asignarToka($idToka,  $datosVehiculo['id']);
            $idTag = $data['datosVehiculo']['num_tag'] === 'null' ? null : $data['datosVehiculo']['num_tag'];
            $this->pvService->asignarTag($idTag, $datosVehiculo['id']);

            if (
                $datosVehiculo['tipo_vehiculo'] == "1"
                || $datosVehiculo['tipo_vehiculo'] == "3"
                || $datosVehiculo['tipo_combustible'] == "4"
            ) {
                $this->pvService->updateTanque($datosTanque, $id_tanque, $id_vehiculo, $numIntercompania);
            }

            if ($data['hasDatosSeguro']) {
                if (isset($data['datosPoliza'])) {
                    $this->pvService->storeDatosPoliza($datosPoliza, $id_vehiculo);
                }
            }

            DB::commit();

            return response()->json([
                "status" => "success",
                "message" => "Datos guardados correctamente",
                "data" => $data
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar los datos',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $vehiculo = DatosVehiculo::where('id', $id)->with('datos_tanque')->first();

        if (!$vehiculo) {
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas eliminar no existe',
                'data' => ''
            ]);
        }

        $vehiculo->update(['activo' => 0]);

        if (!empty($vehiculo->datos_tanque)) {
            $vehiculo->datos_tanque->update(['activo' => 0]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => $vehiculo
        ]);
    }

    public function getComentarios($id)
    {

        $observaciones = ObservacionVehiculo::where('datos_vehiculo_id', $id);

        return response()->json([
            'status' => 'success',
            'data' => $observaciones,
            'message' => 'Observaciones recuperadas correctamente'
        ]);
    }

    /**
     * Recupera un catalogo de datos de autotanques filtrados por num intercompania
     *
     * @param int $intercompania : num intercompania de la empresa
     */
    public function getAutotanques($intercompania)
    {
        $data = DB::select("call SistemaTickets.SP_GetAutotanquesSucursal($intercompania)");

        return response()->json([
            "status" => "Success",
            "data" => $data,
            "message" => "Datos recuperados correctamente"
        ]);
    }

    /**
     * Importa datos de los vehículos y tanques desde un csv
     */
    public function importarCSV(Request $request)
    {
        $request->validate([
            'archivo_csv' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('archivo_csv');
        $path = $file->getRealPath();
        $this->pvService->importar($path);

        return response()->json([
            'mensaje' => 'Importación exitosa'
        ]);
    }


    public function getGastoUnidad($idVehiculo)
    {

        $data = DB::select("call SistemaTickets.SP_GetGastosUnidad($idVehiculo)");

        return response()->json([
            "status" => "Success",
            "data" => $data,
            "message" => "Datos recuperados correctamente"
        ]);
    }

    public function descargarGastosUnidad($idVehiculo){
        $data = DB::select("call SistemaTickets.SP_GetGastosUnidad($idVehiculo)");
        $filename = 'Gastos_vehiculo_'.$idVehiculo.'.xlsx';

         return Excel::download( new GastosVehiculoExport($data), $filename,
            null, ['Content-Disposition' => 'attachment; filename="'.$filename.'"']
        );
    }



    public function importarDatosSeguro(Request $request)
    {
        // Validar que se envió el archivo
       $request->validate([
            'archivo_csv' => 'required|file|mimes:csv,txt',
        ]);

        $archivo = $request->file('archivo_csv');
        $this->pvService->importarDesdeCsv($archivo);

        return response()->json(['mensaje' => 'Importación completada'], 200);
    }

    public function actualizarDatosPV(Request $request)
    {
        $request->validate([ 'archivo_csv' => 'required|file|mimes:csv,txt' ]);

        $archivo = $request->file('archivo_csv');
        $ruta = $archivo->getRealPath();
        $this->pvService->procesarCSV($ruta);

        return response()->json(['mensaje' => 'Importación completada'], 200);
    }

    public function saveObservacionVehiculo($comentario, $id_vehiculo, $userId)
    {
        $dataObservacion = new ObservacionVehiculo();
        $dataObservacion->observaciones = $comentario;
        $dataObservacion->datos_vehiculo_id = $id_vehiculo;
        $dataObservacion->user_id = $userId;
        $dataObservacion->save();
    }

    public function getObservaciones($id)
    {

        $observaciones = ObservacionVehiculo::where('datos_vehiculo_id', $id)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => ObservacionesVehiculoResource::collection($observaciones),
            'message' => 'Observaciones recuperadas correctamente'
        ]);
    }

    public function getDatosPoliza($id)
    {

        $datosPolizas = SeguroVehiculo::where('id_com_datos_vehiculo', $id)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => PolizasSeguroResource::collection($datosPolizas),
            'message' => 'Polizas recuperadas correctamente'
        ]);
    }

    public function autorizarVehiculo(Request $request)
    {
        $data =  request()->all();
        $idVehiculo = $data['idVehiculo'];
        $userId = $request->user()->id;
        $vehiculo = DatosVehiculo::find($idVehiculo);
        if ($vehiculo) {
            $vehiculo->update([
                'activo' => 1,
            ]);

            if (isset($data['observacion']) && !empty($data['observacion'])) {
                $this->saveObservacionVehiculo($data['observacion'], $idVehiculo, $userId);
            }

            return response()->json([
                'status' => 'success',
                'data' => [],
                'message' => "Unidad autorizada correctamente"
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'data' => [],
                'message' => "No se puedo encontrar la unidad que se intenta autorizar"
            ]);
        }
    }


    public function getParqueWithToka($idSucursal){
        $data =  $this->pvService->queryVehiculosForToka($idSucursal);

        return response()->json([
            'status' => 'success',
            'data' => RecargaTokaResource::collection($data),
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    /**
     * Guarda una solicitud de dispersion de diesel generado por la planta
     */
    public function saveSolicitudRecargaToka(Request $request)
    {
        DB::beginTransaction();
        try {
            $userId = $request->user()->id;
            $recargas = $request->captura;
            $solicitud = $request->solicitud;

            // Validar que exista al menos una recarga
            $recargasValidas = $this->getRecargasValidas($recargas);

            if ($recargasValidas->isEmpty()) {
                return response([
                    'message' => 'Debe capturar al menos una recarga.',
                    'status' => 'error'
                ], 422);
            }

            $solicitudDiesel = $this->dispersionDiesel->storeSolicitudDiesel(
                $userId,    $solicitud['periodoInicio'], $solicitud['periodoFin'],
                $solicitud['precioCombustible'],   $solicitud['empresa'],
            );

            foreach ($recargasValidas as $recarga) {
                $this->dispersionDiesel->storeRecargaVehiculo(
                    $recarga['id'], $recarga['abonoNuevo'], $recarga['ventaLitros'],
                    $solicitudDiesel->id, $recarga['id_asignacion']
                );
            }

            $this->dispersionDiesel->notificarDispersion('s', $solicitudDiesel->id);

            DB::commit();

            return response([
                'message' => 'Datos Guardados Correctamente',
                'data' => $solicitudDiesel,
                'status' => 'success'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response([
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    private function getRecargasValidas($recargas){
        return collect($recargas)->filter(function ($recarga) {
                return (
                    ((float)$recarga['ventaLitros'] + (float)$recarga['abonoNuevo']) > 0 && empty($recarga['idSolicitud']));
            });
    }

    private  function getDispersiionValidas($recargas){
        return collect($recargas)->filter(function ($recarga) {
                return (
                    ((float)$recarga['saldoActual'] + (float)$recarga['saldoDispersar']) > 0 && !empty($recarga['idSolicitud'])
                );
            });
    }

    public function saveRecargaToka(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $noDispersion = $data['no_dispersion'];
            $porcentaje = $data['porcentaje'];
            $solicitud = $data['solicitudDiesel'];
            $recargas = $data['saldosDispersar'];

            $recargasValidas =  $this->getDispersiionValidas($recargas);

            if ($recargasValidas->isEmpty()) {
                return response([
                    'message' => 'No existen registros para dispersar.',
                    'status' => 'error'
                ], 422);
            }
            if($noDispersion == 1){
                $this->dispersionDiesel->updateSolicitudDiesel($solicitud['id']);
            }


            foreach ($recargasValidas as $recarga) {
                $row = ComRecargasVehiculos::find($recarga['idSolicitud']);

                if (!$row) {
                    throw new \Exception(
                        "No se encontró la recarga con ID {$recarga['idSolicitud']}"
                    );
                }

                if($noDispersion == 1){
                    $row->monto_autorizado = $recarga['saldoAutorizado'];
                    $row->save();
                }

                $this->dispersionDiesel->storeExibicion(
                    $noDispersion, $recarga['saldoActual'],
                    $recarga['saldoDispersar'], 1, $recarga['idSolicitud'] , 1, null, null, $porcentaje);
            }

            DB::commit();

            return response([
                'message' => 'Datos Guardados Correctamente',
                'data' => [],
                'status' => 'success'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response([
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }


    public function notificarDispersion(Request $request){
       DB::beginTransaction();
        try {
            $data = $request->all();
            $solicitud = $data['solicitudDiesel'];
            $recargas = $data['saldosDispersar'];
            $noDispersion = $data['no_dispersion'];

            $recargasValidas = collect($recargas)->filter(function ($recarga) {
                return (
                    ((float)$recarga['saldoActual'] + (float)$recarga['saldoDispersar']) > 0
                    && !empty($recarga['idSolicitud'])
                );
            });
            if ($recargasValidas->isEmpty()) {
                return response([
                    'message' => 'No existen registros para dispersar.',
                    'status' => 'error'
                ], 422);
            }
            if($noDispersion === $solicitud['exibiciones']
            ){
                $this->dispersionDiesel->updateSolicitudDiesel($solicitud['id'], 3);
            }else{
                $this->dispersionDiesel->updateSolicitudDiesel($solicitud['id'], 4);
            }

            foreach ($recargasValidas as $recarga) {
                $row =  $this->dispersionDiesel->updateExibicion($recarga['idExhibicion'], 2, 1, now());
                if($noDispersion === $solicitud['exibiciones']
                ){
                    $row = $this->dispersionDiesel->updateRecargaVehiculo($recarga['idSolicitud'], 2 );
                }

                if (!$row) {
                    throw new \Exception(
                        "No se encontró la recarga con ID {$recarga['idSolicitud']}"
                    );
                }
            }
            $this->dispersionDiesel->notificarDispersion('d', $solicitud['id']);
            DB::commit();

            return response([
                'message' => 'Datos Guardados Correctamente',
                'data' => [],
                'status' => 'success'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response([
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

}
