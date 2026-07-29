<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Enums\EstatusActivos;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ucoip\Models\CatEmpresas;

/**
 * Models
 */
use Modules\Ucoip\Models\HardwarePcModel;
use Modules\Ucoip\Models\HardwareSoftware;
use Modules\Ucoip\Models\IntercambioHardware;
use Modules\Ucoip\Models\Software;
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
        $existe = HardwarePcModel::where('no_serie', $request->no_serie)->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'El número de serie ya se encuentra registrado.'
            ], 422);
        }

       $hardware = $this->hardwarePC->create([
            "no_inventario" => $this->generarNoInventario($request->empresa),
            "marca" => $request->marca,
            "modelo" => $request->modelo,
            "no_serie" => $request->no_serie,
            "tipo" => $request->tipo_cpu,
            "mac" => $request->mac,
            "memoria_ram" => $request->memoria_ram,
            "disco_duro" => $request->disco_duro,
            "procesador" => $request->procesador,
            "caracteristicas" => $request->caracteristicas,
            "observaciones" => $request->observaciones,
            "estado" => $request->estado,
            "cat_empresa_id" => $request->empresa,
            "cat_hardware_id" => $request->cat_hardware_id,
            "estado_fisico" => $request->estado_fisico ?? null
        ]);

        if (isset($request->licencia_so_id) && !empty($request->licencia_so_id)) {
            $this->sincronizarSoftwareHardware($hardware->id, $request->licencia_so_id);
        }

        if (isset($request->licencia_office_id) && !empty($request->licencia_office_id)) {
            $this->sincronizarSoftwareHardware($hardware->id, $request->licencia_office_id);
        }

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

       $licenciaSoActual     = $this->obtenerLicenciaActiva($id, 1);
       $licenciaOfficeActual = $this->obtenerLicenciaActiva($id, 2);

       if($hardware){
            $hardware->update([
                "marca" => $request->marca,
                "modelo" => $request->modelo,
                "no_serie" => $request->no_serie,
                "tipo" => $request->tipo_cpu,
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

            if(isset($request->licencia_so_id) && !empty($request->licencia_so_id)){
                $this->sincronizarSoftwareHardware($id, $request->licencia_so_id, $licenciaSoActual);
            }

            if(isset($request->licencia_office_id) && !empty($request->licencia_office_id)){
                $this->sincronizarSoftwareHardware($id, $request->licencia_office_id, $licenciaOfficeActual);
            }
       }

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

    public function sincronizarSoftwareHardware($idHardware, $nuevoSoftwareId, $softwareAnteriorId = null)
    {
        if ((int)$nuevoSoftwareId === (int)$softwareAnteriorId) {
            return;
        }
        if (!empty($softwareAnteriorId)) {
            HardwareSoftware::where('ucoip_hardware_id', $idHardware)
                ->where('ucoip_software_id', $softwareAnteriorId)
                ->whereNull('fecha_retiro')
                ->update([
                    'fecha_retiro' => now(),
                ]);
            $this->setEstatusLicencia($softwareAnteriorId, EstatusActivos::DISPONIBLE);
        }

        if (!empty($nuevoSoftwareId)) {
            HardwareSoftware::create([
                'ucoip_hardware_id' => $idHardware,
                'ucoip_software_id' => $nuevoSoftwareId,
                'fecha_asignacion'  => now(),
                'fecha_retiro' => null,
            ]);
            $this->setEstatusLicencia($nuevoSoftwareId, EstatusActivos::ASIGNADA);
        }
    }

    private function obtenerLicenciaActiva($hardwareId, $tipoSoftware)
    {
        return HardwareSoftware::where('ucoip_hardware_id', $hardwareId)
            ->whereHas('software', function ($query) use ($tipoSoftware) {
                $query->where('cat_software_id', $tipoSoftware);
            })
            ->whereNull('fecha_retiro')
            ->value('ucoip_software_id');
    }

    public function setEstatusLicencia($idLicencia, $estatus){
        $licencia =  Software::find($idLicencia);
        if($licencia){
                $licencia->estatus = $estatus;
                $licencia->save();
        }
    }

}
