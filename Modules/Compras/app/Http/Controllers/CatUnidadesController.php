<?php

namespace Modules\Compras\Http\Controllers;

use App\Exports\GastosVehiculoExport;
use App\Http\Controllers\Controller;
use App\Mail\RequisicionDieselMail;
use App\Models\Sucursales;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Exists;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Compras\Models\ComRecargasVehiculos;
use Modules\Compras\Models\DatosGps;
use Modules\Compras\Models\DatosTanque;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Models\ObservacionVehiculo;
use Modules\Compras\Models\SolcitudDiesel;
use Modules\Compras\Models\Tags;
use Modules\Compras\Models\TarjetasToka;
use Modules\Compras\Models\VehiculosTags;
use Modules\Compras\Models\VehiculosToka;
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
                $vehiculo = $this->storeVehiculo($datosVehiculo, $numIntercompania, $userId);
                $idToka = $data['datosVehiculo']['num_tarjeta_toka'] === 'null' ? null : $data['datosVehiculo']['num_tarjeta_toka'];
                $idTag = $data['datosVehiculo']['num_tag'] === 'null' ? null : $data['datosVehiculo']['num_tag'];
                 $this->asignarToka($idToka,  $vehiculo);
                 $this->asignarTag($idTag, $vehiculo);
                if (
                    $datosVehiculo['tipo_vehiculo'] == "1"
                    || $datosVehiculo['tipo_vehiculo'] == "3"
                    || $datosVehiculo['tipo_combustible'] == "4"
                ) {
                    if (isset($data['datosTanque'])) {
                        $this->storeTanque($vehiculo, $datosTanque, $numIntercompania);
                    }
                }

                if ($data['hasDatosSeguro']) {
                    if (isset($data['datosPoliza'])) {
                        $this->storeDatosPoliza($datosPoliza, $vehiculo);
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


            $this->updateVehiculo($datosVehiculo, $id_vehiculo, $userId);

            $idToka = $data['datosVehiculo']['num_tarjeta_toka'] == 'null' ? null : $data['datosVehiculo']['num_tarjeta_toka'];

            $this->asignarToka($idToka,  $datosVehiculo['id']);
            $idTag = $data['datosVehiculo']['num_tag'] === 'null' ? null : $data['datosVehiculo']['num_tag'];
            $this->asignarTag($idTag, $datosVehiculo['id']);

            if (
                $datosVehiculo['tipo_vehiculo'] == "1"
                || $datosVehiculo['tipo_vehiculo'] == "3"
                || $datosVehiculo['tipo_combustible'] == "4"
            ) {
                $this->updateTanque($datosTanque, $id_tanque, $id_vehiculo, $numIntercompania);
            }

            if ($data['hasDatosSeguro']) {
                if (isset($data['datosPoliza'])) {
                    $this->storeDatosPoliza($datosPoliza, $id_vehiculo);
                }
                // $this->updateDatosPoliza($datosPoliza, $id_vehiculo, $id_poliza);s
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

        $vehiculo->update([
            'activo' => 0
        ]);

        if (!empty($vehiculo->datos_tanque)) {
            $vehiculo->datos_tanque->update([
                'activo' => 0
            ]);
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

    /**
     * Almacena los datos del vehículo y retorna el id
     *
     * @param array $data
     * @param array $intercompania
     * @return $dataVehiculo->id
     */
    public function storeVehiculo($data, $intercompania, $userId)
    {

        $datosVehiculos =  $data;

        $dataVehiculo = new DatosVehiculo();

        $dataVehiculo->marca = $datosVehiculos['marca'];
        $dataVehiculo->id_sucursal = $this->getIdSucursal($intercompania);
        $dataVehiculo->submarca = $datosVehiculos['submarca'];
        $dataVehiculo->modelo = $datosVehiculos['modelo'];
        $dataVehiculo->no_serie = $datosVehiculos['no_serie'];
        $dataVehiculo->placas = $datosVehiculos['placas'];
        $dataVehiculo->nro_economico = $datosVehiculos['nro_economico'];
        $dataVehiculo->id_cre = $datosVehiculos['id_cre'];
        $dataVehiculo->tipo_combustible = $datosVehiculos['tipo_combustible'];
        $dataVehiculo->tipo = $datosVehiculos['tipo_vehiculo'];
        $dataVehiculo->num_tarjeta_toka = $datosVehiculos['num_tarjeta_toka'];
        $dataVehiculo->num_tag = $datosVehiculos['num_tag'];
        $dataVehiculo->limite = $datosVehiculos['limite'];
        $dataVehiculo->estatus = $datosVehiculos['estatus'];
        $dataVehiculo->categoria = $datosVehiculos['categoria'];
        $dataVehiculo->gps = $datosVehiculos['gps'];
        $dataVehiculo->capacidad_combustible = $datosVehiculos['tanque_combustible'];
        $dataVehiculo->rendimiento_x_litro = $datosVehiculos['rendimiento'];
        $dataVehiculo->activo = 2;

        $dataVehiculo->save();

        if (isset($datosVehiculos['observacion']) && !empty($datosVehiculos['observacion'])) {
            $this->saveObservacionVehiculo($datosVehiculos['observacion'], $dataVehiculo->id, $userId);
        }

        return $dataVehiculo->id;
    }

    public function asignarToka( $idToka, $idVehiculo)
    {
        DB::transaction(function () use ($idToka, $idVehiculo) {
            $asignacionActual = VehiculosToka::where('com_id_datos_vehiculos', $idVehiculo)->whereNull('fecha_fin')->first();
            // Retirar tarjeta
            if (is_null($idToka)) {
                if ($asignacionActual) {
                    $asignacionActual->update(['fecha_fin' => now()]);
                    TarjetasToka::where('id', $asignacionActual->com_id_tarjetas_toka)->update(['estatus' => '0']);
                }
                return;
            }
            // Validar que la tarjeta no esté asignada
            $tarjetaAsignada = VehiculosToka::where('com_id_tarjetas_toka',$idToka)->whereNull('fecha_fin')->first();

            if ($tarjetaAsignada &&$tarjetaAsignada->com_id_datos_vehiculos != $idVehiculo) {
                throw new \Exception(
                    'La tarjeta ya se encuentra asignada a otro vehículo.'
                );
            }
            // Si el vehículo ya tiene una asignación
            if ($asignacionActual) {
                // Si es la misma tarjeta no hacer nada
                if ($asignacionActual->com_id_tarjetas_toka == $idToka) {
                    return;
                }

                // Finalizar asignación anterior
                $asignacionActual->update(['fecha_fin' => now()
                ]);
                // Liberar tarjeta anterior
                TarjetasToka::where('id',$asignacionActual->com_id_tarjetas_toka)->update([
                    'estatus' => '0'
                ]);
            }
            // Crear nueva asignación
            VehiculosToka::create([
                'com_id_datos_vehiculos' => $idVehiculo,
                'com_id_tarjetas_toka' => $idToka,
                'fecha_inicio' => now(),
            ]);
            // Marcar tarjeta como asignada
            TarjetasToka::where('id', $idToka)->update(['estatus' => '1']);
        });
    }


    public function asignarTag( $idTag, $idVehiculo)
    {
        DB::transaction(function () use ($idTag, $idVehiculo) {
            $asignacionActual = VehiculosTags::where('com_id_datos_vehiculos', $idVehiculo)->whereNull('fecha_fin')->first();
            // Retirar tag
            if (is_null($idTag)) {
                if ($asignacionActual) {
                    $asignacionActual->update(['fecha_fin' => now()]);
                    Tags::where('id', $asignacionActual->com_id_tags)->update(['estatus' => '0']);
                }
                return;
            }
            // Validar que el tag no esté asignado
            $tarjetaAsignada = VehiculosTags::where('com_id_tags',$idTag)->whereNull('fecha_fin')->first();

            if ($tarjetaAsignada &&$tarjetaAsignada->com_id_datos_vehiculos != $idVehiculo) {
                throw new \Exception(
                    'La tarjeta ya se encuentra asignada a otro vehículo.'
                );
            }
            // Si el vehículo ya tiene una asignación
            if ($asignacionActual) {
                // Si es el mismo tag no hacer nada
                if ($asignacionActual->com_id_tags == $idTag) {
                    return;
                }

                // Finalizar asignación anterior
                $asignacionActual->update(['fecha_fin' => now()
                ]);
                // Liberar el tag anterior
                Tags::where('id', $asignacionActual->com_id_tags)->update([
                    'estatus' => '0'
                ]);
            }
            // Crear nueva asignación
            VehiculosTags::create([
                'com_id_datos_vehiculos' => $idVehiculo,
                'com_id_tags' => $idTag,
                'fecha_inicio' => now(),
            ]);
            // Marcar tarjeta como asignada
            Tags::where('id', $idTag)->update(['estatus' => '1']);
        });
    }

    /**
     * Almacena los datos del tanque
     *
     * @param array $id
     * @param array $data
     * @param array $intercompania
     */
    public function storeTanque($id, $data, $intercompania)
    {

        $datosTanque =  $data;

        $dataTanque = new DatosTanque();

        $dataTanque->com_datos_vehiculo_id = $id;
        $dataTanque->marca = $datosTanque['marca_tanque'];
        $dataTanque->id_sucursal = $this->getIdSucursal($intercompania);
        $dataTanque->anio_fabricacion = $datosTanque['anio_fabricacion'];
        $dataTanque->capacidad = $datosTanque['capacidad'];
        $dataTanque->serie = $datosTanque['serie'];
        $dataTanque->tipo_medidor = $datosTanque['tipo_medidor'];

        $dataTanque->save();
    }

    public function storeDatosPoliza($data, $idVehiculo)
    {
        $datosPoliza =  $data;

        $ultimoSeguro = SeguroVehiculo::where('id_com_datos_vehiculo', $idVehiculo)
                                  ->latest('id')
                                  ->first();

        $nuevo = [
            'aseguradora'       => $datosPoliza['aseguradora'],
            'cobertura'         => $datosPoliza['cobertura'],
            'fecha_renovacion'  => $datosPoliza['fecha_emision'],
            'inicio_vigencia'   => $datosPoliza['inicio_vigencia'],
            'fin_vigencia'      => $datosPoliza['fin_vigencia'],
            'flotilla'          => $datosPoliza['numero_poliza'],
            'inciso_foltilla'   => $datosPoliza['inciso'],
            'ramo'              => $datosPoliza['ramo'],
            'sub_ramo'          => $datosPoliza['subramo'],
            'prima_total'       => $datosPoliza['prima_total'],
            'tipo_movimiento'   => $datosPoliza['tipo_movimiento'],
            'periodicidad_pago' => $datosPoliza['periodicidad_pago'],
        ];


        if ($ultimoSeguro) {
            $existente = $ultimoSeguro->only(array_keys($nuevo));

            $diferencias = array_diff_assoc($nuevo, $existente);

            if (empty($diferencias)) {
                return;
            }
        }

        $dataPoliza = new SeguroVehiculo($nuevo);
        $dataPoliza->id_com_datos_vehiculo = $idVehiculo;
        $dataPoliza->save();
    }

    public function updateVehiculo($data, $id, $userId)
    {
        $vehiculo = DatosVehiculo::find($id);
        if (!$vehiculo) {
            throw new \Exception("Vehiculo no encontrado");
        }

        $vehiculo->update([
            'marca' => $data['marca'],
            'submarca' => $data['submarca'],
            'modelo' => $data['modelo'],
            'no_serie' => $data['no_serie'],
            'placas' => $data['placas'],
            'tipo' => $data['tipo_vehiculo'],
            'nro_economico' => $data['nro_economico'],
            'id_cre' => $data['id_cre'],
            'tipo_combustible' => $data['tipo_combustible'],
            'estatus' => $data['estatus'],
            'categoria' => $data['categoria'],
            'gps' => $data['gps'],
            'num_tarjeta_toka' => $data['num_tarjeta_toka'],
            'num_tag' => $data['num_tag'],
            'limite' => $data['limite'],
            'capacidad_combustible' => $data['tanque_combustible'],
            'rendimiento_x_litro' => $data['rendimiento'],
        ]);

        if (isset($data['observacion']) && !empty($data['observacion'])) {
            $this->saveObservacionVehiculo($data['observacion'], $id, $userId);
        }
    }

    public function updateTanque($data, $id, $numIntercompania, $id_vehiculo)
    {

        $tanque = DatosTanque::find($id);

        if (!$tanque || empty($id)) {
            $this->storeTanque($id_vehiculo, $data, $numIntercompania);
        } else {
            $tanque->update([
                'marca' => $data['marca_tanque'],
                'anio_fabricacion' => $data['anio_fabricacion'],
                'capacidad' => $data['capacidad'],
                'serie' => $data['serie'],
                'tipo_medidor' => $data['tipo_medidor']
            ]);
        }
    }

    public function updateDatosPoliza($data, $id_vehiculo, $idPoliza)
    {
        $poliza = SeguroVehiculo::find($idPoliza);


        if (!$poliza || empty($poliza)) {
            $this->storeDatosPoliza($data, $id_vehiculo);
        } else {
            $poliza->update([

                'aseguradora' => $data['aseguradora'],
                'inciso_vehiculo' => $data['inciso_vehiculo'],
                'cobertura' => $data['cobertura'],
                'inicio_vigencia' => $data['inicio_vigencia'],
                'fin_vigencia' => $data['fin_vigencia'],
                'flotilla' => $data['flotilla'],
                'inciso_foltilla' => $data['inciso_foltilla'],

            ]);
        }
    }

    public function getIdSucursal($intercompania)
    {
        $sucursal = Sucursales::where('num_intercompania', $intercompania)->first();
        return $sucursal->id;
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
        $request->validate([
            'archivo_csv' => 'required|file|mimes:csv,txt',
        ]);

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
                    ((float)$recarga['ventaLitros'] + (float)$recarga['abonoNuevo']) > 0
                    && empty($recarga['idSolicitud'])
                );
            });
    }

    private  function getDispersiionValidas($recargas){
        return collect($recargas)->filter(function ($recarga) {
                return (
                    ((float)$recarga['saldoActual'] + (float)$recarga['saldoDispersar']) > 0
                    && !empty($recarga['idSolicitud'])
                );
            });
    }

    public function saveRecargaToka(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $noDispersion = $data['no_dispersion'];
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
                // $row->fecha_dispersion = now();
                /**
                 * Si se trata de la primera dispersion permite
                 * establecer el limite de saldo autorizado
                 */
                if($noDispersion == 1){
                    $row->monto_autorizado = $recarga['saldoAutorizado'];
                    $row->save();
                }

                // $row->monto_dispersado = $recarga['saldoDispersar'];
                // $row->saldo_actual = $recarga['saldoActual'];


                $this->dispersionDiesel->storeExibicion(
                    $noDispersion, $recarga['saldoActual'],
                    $recarga['saldoDispersar'], 1, $recarga['idSolicitud'] , 1, null, null);
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
