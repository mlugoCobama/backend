<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
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
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('compras::show');
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
            'marca' => $row[0] ?? 'NA',
            'submarca' =>  $row[1] ?? 'NA',
            'modelo' => $row[2] ?? 'NA',
            'no_serie' =>  $row[3] ?? 'NA',
            'placas' => $row[4] ?? 'NA', 
            'id_sucursal' =>  10 ?? 'NA',
        ]);
            
        DatosTanque::create([
            'marca' => $row[5] ?? 'NA', 
            'anio_fabricacion' => $row[6] ?? 'NA',
            'capacidad' => $row[7] ?? 'NA',
            'serie' => $row[8] ?? 'NA',
            'tipo_medidor' => $row[9] ?? 'NA',
            'id_sucursal' => 10,
            'com_datos_vehiculo_id' => $auto->id,
        ]);
    }

    fclose($handle);

    return response()->json([
        'mensaje' => 'Importación exitosa'
    ]);
}


}
