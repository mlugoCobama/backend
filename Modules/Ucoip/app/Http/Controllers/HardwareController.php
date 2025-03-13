<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
/**
 * Models
 */
use Modules\Ucoip\Models\HardwarePcModel;
use Modules\Ucoip\Transformers\HardwareResource;

class HardwareController extends Controller
{
    private $hardwarePC;

    public function __construct(HardwarePcModel $hardwarePC) {
        $this->hardwarePC = $hardwarePC;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => HardwareResource::collection($this->hardwarePC->all())
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->hardwarePC->create([
            "marca" => $request->marca,
            "modelo" => $request->modelo,
            "no_serie" => $request->no_serie,
            "tipo" => $request->tipo,
            "mac" => $request->mac,
            "memoria_ram" => $request->memoria_ram,
            "disco_duro" => $request->disco_duro,
            "procesador" => $request->procesador,
            "caracteristicas" => $request->caracteristicas,
            "observaciones" => $request->observaciones,
            "estado" => $request->estado,
            "cat_hardware_id" => $request->cat_hardware_id
        ]);
        /*
            $archivo = fopen("C:\Users\mlugo\Desarrollos\backend-dashboard\Modules\Ucoip\app\Http\Controllers\\teclados.csv", "r");
            
            while (($fila = fgetcsv($archivo, 1000, ",")) !== FALSE) {
                
                $tipo = 0;

                switch ($fila[2]) {
                    case 'LAPTOP':
                        $tipo = 3;
                        break;
                    case 'COMPU  DE MARCA':
                        $tipo = 1;
                        break;
                    case 'COMPU  ARMADA':
                        $tipo = 2;
                        break;
                    case 'TERMINAL':
                        $tipo = 4;
                        break;
                    case 'ALL IN ONE':
                        $tipo = 5;
                        break;
                    case 'LAPTOP CONSULT':
                        $tipo = 6;
                        break;
                    default:
                        $tipo = 0;
                        break;
                }
                
                $this->hardwarePC->create([
                    "marcar" => "Marca",
                    "modelo" => $fila[0],
                    "no_serie" => $fila[1],
                    "tipo" => 0,
                    "mac" => "",
                    "memoria_ram" => "",
                    "disco_duro" => "",
                    "procesador" => "",
                    "caracteristicas" => "",
                    "observaciones" => "",
                    "estado" => 1,
                    "cat_hardware_id" => 3
                ]);

            }
        */
        return response()->json([
            'success' => true,
            'message' => 'Se ha completado la tarea satisfactoriamente',
            'data' => []
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        //

        return response()->json([
            
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //

        return response()->json([]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //

        return response()->json([]);
    }
}
