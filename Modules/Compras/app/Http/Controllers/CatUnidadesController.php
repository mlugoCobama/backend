<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Sucursales;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Models\DatosTanque;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Models\DetallesCotizacion;
use Modules\Compras\Models\ObservacionVehiculo;
use Modules\Compras\Transformers\DetallesCotizacionResource;
use Modules\Compras\Transformers\ObservacionesVehiculoResource;
use Modules\Compras\Transformers\PolizasSeguroResource;
use Modules\Compras\Transformers\VehiculosTanquesResources;
use Modules\Macro\Models\SeguroVehiculo;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Else_;

class CatUnidadesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = VehiculosTanquesResources::collection(DB::select("call SistemaTickets.SP_GetDataAutotanques()"));
        // $data = DatosVehiculo::with('datos_tanque')->get();
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
            $datosVehiculo = $data['datosVehiculo'];
            $datosTanque = $data['datosTanque'] ?? null;
            $datosPoliza = $data['datosPoliza'] ?? null;
            $numIntercompania = $data['intercompania'];

            DB::beginTransaction();
            if (isset($data['datosVehiculo'])) {
                $vehiculo = $this->storeVehiculo($datosVehiculo, $numIntercompania, $userId);

                if (
                    $datosVehiculo['tipo_vehiculo'] == "reparto"
                    || $datosVehiculo['tipo_vehiculo'] == "autotanque"
                    || $datosVehiculo['tipo_combustible'] == "gas_lp"
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
    public function show(Request $request, $id)
    {
        $usuario = $request->user()->id;

        $allAcces = [2247, 2395];

        if (in_array($usuario, $allAcces)) {
            $limite =  2;
        } else {
            $limite =  1;
        }

        $data = VehiculosTanquesResources::collection(DB::select("call SistemaTickets.SP_GetDataAutotanques($id, $limite)"));
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

            if (
                $datosVehiculo['tipo_vehiculo'] == "reparto"
                || $datosVehiculo['tipo_vehiculo'] == "auto tanque"
                || $datosVehiculo['tipo_combustible'] == "gas_lp"
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
        $handle = fopen($path, 'r');

        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {

            $auto = DatosVehiculo::create([
                'nro_economico' => $row[0] ?? 'NA',
                'estatus' => $row[2] ?? 'NA',
                'marca' => $row[3] ?? 'NA',
                'submarca' =>  $row[4] ?? 'NA',
                'modelo' => $row[5] ?? 'NA',
                'no_serie' =>  $row[6] ?? 'NA',
                'placas' => $row[7] ?? 'NA',
                'id_sucursal' =>  $row[13],
            ]);

            DatosTanque::create([
                'marca' => $row[8] ?? 'NA',
                'anio_fabricacion' => $row[9] ?? 'NA',
                'capacidad' => $row[10] ?? 'NA',
                'serie' => $row[11] ?? 'NA',
                'tipo_medidor' => $row[12] ?? 'NA',
                'id_sucursal' => $row[13],
                'com_datos_vehiculo_id' => $auto->id,
            ]);
        }

        fclose($handle);

        return response()->json([
            'mensaje' => 'Importación exitosa'
        ]);
    }

    /**
     * Almacena los datos del vehículo y retorna el id
     * 
     * @param array $data 
     * @param array $intercompania
     * @return number $dataVehiculo->id
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
        $dataVehiculo->estatus = $datosVehiculos['estatus'];
        $dataVehiculo->activo = 2;

        $dataVehiculo->save();

        if (isset($datosVehiculos['observacion']) && !empty($datosVehiculos['observacion'])) {
            $this->saveObservacionVehiculo($datosVehiculos['observacion'], $dataVehiculo->id, $userId);
        }

        return $dataVehiculo->id;
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
        $dataPoliza = new SeguroVehiculo();
        $dataPoliza->aseguradora = $datosPoliza['aseguradora'];
        $dataPoliza->inciso_vehiculo = $datosPoliza['inciso_vehiculo'];
        $dataPoliza->cobertura = $datosPoliza['cobertura'];
        $dataPoliza->inicio_vigencia = $datosPoliza['inicio_vigencia'];
        $dataPoliza->fin_vigencia = $datosPoliza['fin_vigencia'];
        $dataPoliza->flotilla = $datosPoliza['flotilla'];
        $dataPoliza->inciso_foltilla = $datosPoliza['inciso_foltilla'];
        $dataPoliza->fecha_renovacion = $datosPoliza['fecha_renovacion'];
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



    public function importarDatosSeguro(Request $request)
    {
        // Validar que se envió el archivo
        $request->validate([
            'archivo_csv' => 'required|file|mimes:csv,txt',
        ]);

        $archivo = $request->file('archivo_csv');
        $ruta = $archivo->getRealPath();

        $handle = fopen($ruta, 'r');
        $encabezados = fgetcsv($handle);

        while (($fila = fgetcsv($handle)) !== false) {
            $datos = array_combine($encabezados, $fila);

            $vehiculo = DatosVehiculo::where('no_serie', $datos['SERIE'])->first();

            if ($vehiculo) {
                SeguroVehiculo::create([
                    'id_com_datos_vehiculo' => $vehiculo->id,
                    'aseguradora' => 'Banorte',
                    'inciso_vehiculo' => $datos['INCISOV'] ?? null,
                    'cobertura' => $datos['COBERTURA'] ?? null,
                    'inicio_vigencia' => $datos['VIGENCIAI'] ?? null,
                    'fin_vigencia' => $datos['VIGENCIAF'] ?? null,
                    'inciso_foltilla' => $datos['INCISOF'] ?? null,
                    'flotilla' => $datos['FLOTILLA'] ?? null,
                    'fecha_renovacion' => $datos['RENOVACION'] ?? null,
                    'activo' => 1,

                ]);
            }
        }

        fclose($handle);

        return response()->json(['mensaje' => 'Importación completada'], 200);
    }

    public function actualizarDatosPV(Request $request)
    {
        // Validar que se envió el archivo
        $request->validate([
            'archivo_csv' => 'required|file|mimes:csv,txt',
        ]);

        $archivo = $request->file('archivo_csv');
        $ruta = $archivo->getRealPath();

        $handle = fopen($ruta, 'r');
        $encabezados = fgetcsv($handle);

        $status = [
            "ACTIVA" => 1,
            "EN TALLER" => 2,
            "VENDIDA" => 3,
            "NO IDENTIFICADA" => 4,
            "FUERA DE CIRCULACION" => 0,
            "DESCOMPUESTA" => 5,
            "FISCALIA" => 6,
            "CORRALON" => 7,
            "CHATARRA" => 8,
            "VENDIDA  COMO CHATARRA" => 9,
            "BAJA" => 10
        ];

        while (($fila = fgetcsv($handle)) !== false) {
            $datos = array_combine($encabezados, $fila);



            if (isset($datos['numero_serie_v']) && !empty($datos['numero_serie_v'])) {
                $vehiculo = DatosVehiculo::where('no_serie', $datos['numero_serie_v'])->first();

                if ($vehiculo) {
                    // Actualizar datos del vehiculo
                    $vehiculo->update([
                        "id_cre" => $datos['id_cre'],
                        // "nro_economico " => $datos['no_economico'],
                        // "marca" => $datos['marca_v'],
                        // "submarca" => $datos['submarca_v'],
                        // "modelo" => $datos['modelo_v'],
                        // "no_serie" => $datos['numero_serie_v'],
                        // "placas" => $datos['placas'],
                        // "id_sucursal" => $datos['id_sucursal'],
                        "tipo" => strtolower($datos['uso_vehiculo']),
                        "estatus" => $status[$datos['status']] ?? 1,
                        "propietario" => $datos['propietario']
                    ]);
                } else {
                    if (!empty($datos['numero_serie_v'])) {
                        // Crear nuevo vehículo
                        $auto = DatosVehiculo::create([
                            "id_cre" => $datos['id_cre'] ?? null,
                            "nro_economico " => $datos['no_economico'] ?? null,
                            "marca" => $datos['marca_v'] ?? null,
                            "submarca" => $datos['submarca_v'] ?? null,
                            "modelo" => $datos['modelo_v'] ?? null,
                            "no_serie" => $datos['numero_serie_v'] ?? null,
                            "placas" => $datos['placas'] ?? null,
                            "id_sucursal" => $datos['id_sucursal'],
                            "tipo" => $datos['uso_vehiculo'] ?? null,
                            "estatus" => $status[$datos['status']] ?? 1,
                            "propietario" => $datos['propietario'] ?? null
                        ]);

                        if ($datos['uso_vehiculo'] == 'autotanque') {
                            DatosTanque::create([
                                'marca' => $datos['marca_t'] ?? 'NA',
                                'anio_fabricacion' => $datos['anio_fab'] ?? 'NA',
                                'capacidad' => $datos['capacidad'] ?? 'NA',
                                'serie' => $datos['numero_serie_t'] ?? 'NA',
                                'tipo_medidor' => $datos['tipo_medidor'] ?? 'NA',
                                'id_sucursal' => $datos['id_sucursal'],
                                'com_datos_vehiculo_id' => $auto->id,
                            ]);
                        }
                    }
                }
            }
        }

        fclose($handle);

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
}
