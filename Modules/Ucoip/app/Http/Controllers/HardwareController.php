<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ucoip\Models\CatEmpresas;

/**
 * Models
 */
use Modules\Ucoip\Models\HardwarePcModel;
use Modules\Ucoip\Models\IntercambioHardware;
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
       $data = HardwarePcModel::usuarios()
            ->with([
                'Tipo',
                'empresa',
                'asignacion.userGlpi',
                'cambiosHardware.detalle',
                'intercambios.origen',
                'intercambios.destino',
                'asignacionActual.userGlpi'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => HardwareResource::collection($data)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->hardwarePC->create([
            "no_inventario" => $this->generarNoInventario($request->empresa),
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
            "cat_empresa_id" => $request->empresa,
            "cat_hardware_id" => $request->cat_hardware_id,
            "estado_fisico" => $request->estado_fisico
        ]);

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
    public function update(Request $request,$id )
    {
       $hardware = $this->hardwarePC->find($id);

       //Si se detecta que el activo ha cambiado de empresa en referencia a su empresa actual registramos el cambio
       if((int) $hardware->cat_empresa_id !== (int) $request->empresa){
        $this->storeIntercambio($hardware->id, $hardware->cat_empresa_id, $request->empresa );                    
       }

        $hardware->update([
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
            "cat_empresa_id" => $request->empresa,
            "cat_hardware_id" => $request->cat_hardware_id,
            "estado_fisico" => $request->estado_fisico
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Se ha completado la tarea satisfactoriamente',
            'data' => []
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //

        return response()->json([]);
    }

    public function storeIntercambio($idHardware, $idEmpOrigen, $idEmpDestino){
        $intercambio =  new IntercambioHardware();
        $intercambio->empresa_origen = $idEmpOrigen;
        $intercambio->empresa_destino = $idEmpDestino;
        $intercambio->ucoip_hardware_id = $idHardware;
        $intercambio->fecha_traspaso = now();
        $intercambio->save();
    } 


    public function generarNoInventario($idEmpresa)
    {
        $empresa = CatEmpresas::findOrFail($idEmpresa);
        $ultimoRegistro = HardwarePcModel::where('cat_empresa_id', $idEmpresa)
            ->orderByDesc('id')
            ->first();
        if (!$ultimoRegistro) {
            $consecutivo = 1;
        } else {
            $ultimoConsecutivo = intval(substr($ultimoRegistro->no_inventario, -6));
            $consecutivo = $ultimoConsecutivo + 1;
        }
        return $empresa->intercompania . '-' . str_pad($consecutivo, 6, '0', STR_PAD_LEFT);
    }
}
