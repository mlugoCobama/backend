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
        $response = Http::withHeaders([
            'x-api-key' => env('TAC_API_KEY'),
            'Accept' => 'application/json',
            ])->get(env('TAC_API_ROUTE'), [
                'site' => $id
            ]);

        $data = $response->json();

        $formatData = [];

        foreach ($data as $item) {

            $equipo =  $this->separarMarcaModelo($item['make_model']);

            $ucoip = $this->findUcoipGlpi($item['logged_username'], $item['site_name']);

            $formatData[] = [
                'marca' => $equipo ['marca'],
                'modelo' => $equipo ['modelo'],
                'no_serie' => $item['serial_number'],
                'tipo' => $this->validateOrigin($item['serial_number']),
                'disco_duro' => $this->obtenerCapacidad($item['physical_disks'][0] ?? 'OGB'),
                'procesador' => $item['cpu_model'][0],
                'cat_hardware_id' => 1,
                'cat_empresa_id' => 14,

                'nombre_equipo' => $item['hostname'],
                // 'ucoip' => $ucoip,
                'empresa' => $item['site_name'],
                'usuario' => $item['logged_username'],
                // 'dsc' => $item['make_model'],
                // 'sistema' => $item['plat'],
                // 'dsc_sistema' => $item['operating_system'],
                
                // 'tarjeta_grafica' => $item['graphics'],
            ];
        }

        foreach ($formatData as $data) {

          $inventario = $this->hwService->storeHardware($data);
          $dataUcoip =  $this->findUcoipGlpi($data['usuario'], $data['empresa']);

          $resguardo = $this->resguardoService->storeResguardo($dataUcoip);
          $this->resguardoService->storeDetalle($inventario->id, [], $resguardo->id);
        }

        return response()->json([
            'status' => $response->status(),
            // 'rawData' => $data,
            'data' => $formatData,
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    public function validateOrigin($serie){
        return $serie == "To be filled by O.E.M." ? 2 : 1;
    }


    public function obtenerCapacidad(string $texto)
    {
        preg_match('/(\d+(?:\.\d+)?(?:GB|TB))/', $texto, $matches);
        return $matches[1] ?? null;
    }

    public function separarMarcaModelo(string $texto): array
    {
        $texto = trim($texto);
        $partes = explode(' ', $texto);
        $marca = $partes[0] ?? null;

        $modelo = implode(' ', array_slice($partes, 1));

        $modelo = str_replace([
            'Technology Co., Ltd.',
            'Inc.',
            'Corporation',
            'COMPUTER INC.',
            'Ltd.',
            'Co.,'
        ], '', $modelo);

        $modelo = trim(preg_replace('/\s+/', ' ', $modelo));

        return [
            'marca' => $marca,
            'modelo' => $modelo
        ];
    }

    public function findUcoipGlpi($ucoip, $nombreSite){
        $empresa = ModelsCatEmpresas::where('nombre', 'like', '%' . $nombreSite . '%')->first();
        
        $data = [
            'id_usuario' => null,
            'id_empresa' => null
        ];

        if($empresa){
            $usuario = DB::connection('intranet')->select('CALL SP_GetUsuarioEmail(?)', [$ucoip.'@'.$empresa->dominio]);
            $data = [
            'id_usuario' => $usuario[0]->id ?? null,
            'id_empresa' => $empresa->id ?? null 
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
        $resguardo = Resguardo::with(['detalles.hardware.tipo'])->where('id_usuario_asignado', $id)->get();

        return response()->json([
            'success' => 'Datos recuperados correctamente',
            'data' => $resguardo,
        ]);
    }
}
