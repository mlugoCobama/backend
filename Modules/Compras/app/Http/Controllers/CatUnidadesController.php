<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Sucursales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Models\DatosTanque;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Transformers\VehiculosTanquesResources;

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

        try {
            $datosVehiculo = $data['datosVehiculo'];
            $datosTanque = $data['datosTanque'];
            $numIntercompania = $data['intercompania'];

            DB::beginTransaction();

            $vehiculo = $this->storeVehiculo($datosVehiculo, $numIntercompania);

            if($datosVehiculo['tipo_vehiculo'] == "reparto"){
                $this->storeTanque($vehiculo, $datosTanque, $numIntercompania);
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
    public function show($id)
    {
        $data = VehiculosTanquesResources::collection(DB::select("call SistemaTickets.SP_GetDataAutotanques($id)"));
        return response()->json([
            "status" => "Success",
            "data" => $data,
            "message" => "Datos recuperados correctamente"
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('compras::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        try {
            $datosVehiculo = $data['datosVehiculo'];
            $datosTanque = $data['datosTanque'];
            $numIntercompania = $id;

            DB::beginTransaction();

            $this->updateVehiculo($datosVehiculo, $datosVehiculo['id']);

            if($datosVehiculo['tipo_vehiculo'] == "reparto"){
                $this->updateTanque($datosTanque, $datosTanque['id']);
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
        
        if(!$vehiculo){
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

    /**
     * Recupera un catalogo de datos de autotanques filtrados por num intercompania
     * 
     * @param int $intercompania : num intercompania de la empresa
     */
    public function getAutotanques($intercompania) {
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
    public function storeVehiculo($data, $intercompania){

        $datosVehiculos =  $data;

        $dataVehiculo = new DatosVehiculo();

        $dataVehiculo->marca = $datosVehiculos['marca'];
        $dataVehiculo->id_sucursal = $this->getIdSucursal($intercompania);
        $dataVehiculo->submarca = $datosVehiculos['submarca'];
        $dataVehiculo->modelo = $datosVehiculos['modelo'];
        $dataVehiculo->no_serie = $datosVehiculos['no_serie'];
        $dataVehiculo->placas = $datosVehiculos['placas'];
        $dataVehiculo->tipo = $datosVehiculos['tipo_vehiculo'];
        $dataVehiculo->save();

        return $dataVehiculo->id;

    }

    /**
     * Almacena los datos del tanque
     * 
     * @param array $id
     * @param array $data 
     * @param array $intercompania
     */
    public function storeTanque( $id, $data, $intercompania){

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

    public function updateVehiculo($data, $id){
        $vehiculo = DatosVehiculo::find($id);
        if (!$vehiculo) {
            throw new \Exception("Proveedor no encontrado");
        }

        $vehiculo->update([
        'marca' => $data['marca'],
        'submarca' => $data['submarca'],
        'modelo' => $data['modelo'],
        'no_serie' => $data['no_serie'],
        'placas' => $data['placas'],
        'tipo' => $data['tipo_vehiculo'],
        ]);

    }

    public function updateTanque($data, $id){
        $tanque = DatosTanque::find($id);
        if (!$tanque) {
            throw new \Exception("Proveedor no encontrado");
        }

        $tanque->update([

        'marca' => $data['marca_tanque'],
        'anio_fabricacion' => $data['anio_fabricacion'],
        'capacidad' => $data['capacidad'],
        'serie' => $data['serie'],
        'tipo_medidor' => $data['tipo_medidor']

        ]);
    }

    public function getIdSucursal($intercompania){
        $sucursal = Sucursales::where('num_intercompania', $intercompania)->first();
        return $sucursal->id;
    }


    public function getGastoUnidad($idVehiculo){

        $data = DB::select("call SistemaTickets.SP_GetGastosUnidad($idVehiculo)");

        return response()->json([
            "status" => "Success",
            "data" => $data,
            "message" => "Datos recuperados correctamente"
        ]);
    }
}
