<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CatEmpresas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Ucoip\Models\CatEmpresas as ModelsCatEmpresas;
use Modules\Ucoip\Models\CatHardwareModel;
use Modules\Ucoip\Models\Resguardo;
use Modules\Ucoip\Services\HardwareService;
use Modules\Ucoip\Services\ResguardosService;

use Modules\Ucoip\Transformers\CatHardwareResource;

class CatHardwareController extends Controller
{
    private $catHardware;
    private $hwService;
    private $resguardoService;

    public function __construct(CatHardwareModel $catHardware, HardwareService $hwService,  ResguardosService $resguardoService) {
        $this->catHardware = $catHardware;
        $this->hwService = $hwService;
        $this->resguardoService = $resguardoService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => CatHardwareResource::collection($this->catHardware->all())
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        return response()->json([]);
    }

    /**
     * Recupera os datos del tactical
     */
    public function show($id)
    {
        $data = $this->hwService->getDevicesEmpresa($id);
        $formatData = [];

        foreach ($data as $item) {

            $equipo =  $this->hwService->separarMarcaModelo($item['make_model']);
            $ucoip = $this->findUcoipGlpi($item['logged_username'], $item['site_name']);

            $formatData[] = [
                'marca' => $equipo ['marca'],
                'modelo' => $equipo ['modelo'],
                'no_serie' => $item['serial_number'],
                'tipo' => $this->hwService->validateOrigin($item['serial_number']),
                'disco_duro' => $this->hwService->obtenerCapacidad($item['physical_disks'][0] ?? 'OGB'),
                'procesador' => $item['cpu_model'][0],
                'cat_hardware_id' => 1,
                'cat_empresa_id' => $ucoip['id_empresa'],
                'nombre_equipo' => $item['hostname'],
                'empresa' => $item['site_name'],
                'usuario' => $item['logged_username'].' '.'correo'. $ucoip['correo'],
                'idUcoip' => $ucoip['id_usuario'],
                'correo' => $ucoip['correo']

            ];
        }

        foreach ($formatData as $item) {
          $inventario = $this->hwService->storeHardware($item);
        //   $datoUcoip =  $this->findUcoipGlpi($item['correo'], $item['empresa']);
        //   $resguardo = $this->resguardoService->storeResguardo(
        //     ['id_usuario' => $item['idUcoip'],
        //     'id_empresa' => $item['cat_empresa_id']]);
        //   $this->resguardoService->storeDetalle($inventario->id, [], $resguardo->id);

        if(
            !empty($item['idUcoip'])
        ){
            $this->resguardoService->asignarRecurso($inventario->id, null, $item['idUcoip']  );
            $this->hwService->updateEstatusHardware($inventario->id, 2);
        }
        

        }

        return response()->json([
            'status' => 'success',
            'data' => $formatData,
            'data2' => $data,
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    public function findUcoipGlpi($ucoip, $nombreSite){
        $empresa = ModelsCatEmpresas::where('nombre', 'like', '%' . $nombreSite . '%')->first();

        $data = [
            'id_usuario' => null,
            'id_empresa' => null ?? 15,
            'correo' => null
        ];

        if($empresa){
            $usuario = DB::connection('intranet')->select('CALL SP_GetUsuarioEmail(?)', [$ucoip.'@'.$empresa->dominio]);
            $data = [
                'id_usuario' => $usuario[0]->id ?? null,
                'id_empresa' => $empresa->id ?? 15,
                'correo' => $ucoip.'@'.$empresa->dominio
            ];
        }

        return $data;
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

    public function getCatalogoDisponible(){
        $data = CatHardwareModel::with('hardwareDisponible')->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Hardware disponible recuperado correctamente'
        ]);
    }
}
