<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Enums\EstatusActivos;
use App\Http\Controllers\Controller;
use App\Models\CatEmpresas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Ucoip\Models\CatCheckListMantenimientos;
use Modules\Ucoip\Models\CatEmpresas as ModelsCatEmpresas;
use Modules\Ucoip\Models\CatHardwareModel;
use Modules\Ucoip\Models\RecursosRedUcoip;
use Modules\Ucoip\Models\Resguardo;
use Modules\Ucoip\Models\Software;
use Modules\Ucoip\Models\SoftwareUcoip;
use Modules\Ucoip\Services\CsvReaderService;
use Modules\Ucoip\Services\HardwareService;
use Modules\Ucoip\Services\ResguardosService;
use Modules\Ucoip\Services\UcoipService;
use Modules\Ucoip\Transformers\CatHardwareResource;

use Illuminate\Support\Facades\Log;
use Modules\Ucoip\Services\ImportacionHardwareService;

class CatHardwareController extends Controller
{
    private $catHardware;
    private $hwService;
    private $resguardoService;
    private $csvService;
    private $ucoipService;
    private $importacionHardwareService;

    public function __construct(
        ImportacionHardwareService $importacionHardwareService,
        CatHardwareModel $catHardware, HardwareService $hwService,
    ResguardosService $resguardoService, CsvReaderService $csvService,
    UcoipService $ucoipService
    ) {
        $this->catHardware = $catHardware;
        $this->hwService = $hwService;
        $this->resguardoService = $resguardoService;
        $this->csvService = $csvService;
        $this->ucoipService = $ucoipService;
        $this->importacionHardwareService = $importacionHardwareService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->catHardware->usuarios()->get();



        return response()->json([
            'success' => true,
            'message' => '',
            'data' => CatHardwareResource::collection($data)
        ]);
    }

    public function store(Request $request)
    {
        try {

            $this->importacionHardwareService->importar(
                archivo: $request->file('archivo'),
                division: $request->division
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Archivo importado correctamente'
            ]);

        } catch (\Throwable $e) {

            Log::error($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error durante la importación.'
            ], 500);
        }
    }




    /**
     * Recupera os datos del tactical
     */
    public function show($id)
    {
        $data = $this->hwService->getDevicesEmpresa($id);
        // $formatData = [];
        return response()->json([
            'status' => 'success',
            'data2' => $data,
            'devices' => count($data),
            'message' => 'Datos recuperados correctamente'
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

    }

    public function getCatInfra(){
        $data = $this->catHardware->infraestructura()->get();
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => CatHardwareResource::collection($data)
        ]);
    }

    public function getCatalogoDisponible($idEmpresa){
        $data = CatHardwareModel::usuarios()->with([
            'hardwareDisponible' => function ($query) use ($idEmpresa) {
                $query->where('cat_empresa_id', $idEmpresa);
            }
        ])->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Hardware disponible recuperado correctamente'
        ]);
    }

    public function getCatalogoMantenimientos(){
        $data = CatCheckListMantenimientos::where('activo', '1')
            ->orderBy('tipo')
            ->orderBy('orden')
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Hardware disponible recuperado correctamente'
        ]);
    }

    public function asignarRecursoRed($hardwareId, $valor, $restrictivo, $observaciones, $idUcoip, $idTipo)
    {
        $asignacion                         =  new RecursosRedUcoip();
        $asignacion->equipo_id              =  $hardwareId ?? null;
        $asignacion->valor                  =  $valor;
        $asignacion->nivel_restrictivo      =  $restrictivo ?? null;
        $asignacion->observaciones          =  $observaciones ?? null;
        $asignacion->fecha_asignacion       =  now();
        $asignacion->ucoip_ucoip_id         =  $idUcoip;
        $asignacion->ucoip_cat_recursos_id  =  $idTipo;

        $asignacion->save();
    }

    public function storeSoftware($empresa,$version, $licencia, $cat_software_id, $tipo_licencia,$fecha_adquisicion,$estatus ){
        $software = Software::create([
                "empresa" => $empresa,
                "version" => $version,
                "licencia" => $licencia ?? 'N/D' ,
                // "observaciones" => $observaciones,
                "cat_software_id" => $cat_software_id,
                "tipo_licencia" => $tipo_licencia ,
                // "cuenta" => $cuenta,
                // "pass_cuenta" => $pass_cuenta,
                "fecha_adquisicion" => $fecha_adquisicion ??  now(),
                "estatus" => $estatus ?? EstatusActivos::DISPONIBLE
            ]);

        return $software;
    }

    public function asignarSoftware($idUcoip, $idSoftware ){
        $asignacion = new SoftwareUcoip();
        $asignacion->ucoip_ucoip_id = $idUcoip;
        $asignacion->ucoip_software_id = $idSoftware;
        $asignacion->fecha_asignacion = now();
        $asignacion->save();

        $this->updateStatusSoftware($idSoftware, EstatusActivos::ASIGNADA);
    }

    public function updateStatusSoftware($id, $status){
        $software = Software::find($id);

        if($software){
            $software->estatus = $status;
            $software->save();
        }
    }

}
