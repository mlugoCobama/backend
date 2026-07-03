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
        return response()->json([
            'status' => 'success',
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
