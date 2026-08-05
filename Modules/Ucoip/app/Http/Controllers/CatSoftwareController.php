<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Enums\EstatusActivos;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Ucoip\Models\CatSoftware;
use Modules\Ucoip\Models\Software;
use Modules\Ucoip\Services\SoftwareService;
use Modules\Ucoip\Transformers\SoftwareResource;

class CatSoftwareController extends Controller
{
    protected $softwareService;
    public function __construct(SoftwareService $softwareService)
    {
        $this->softwareService = $softwareService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Software::with(['tipoSoftware', 'sucursal'])->active()->get();
        $tipos =  CatSoftware::get();

        return response()->json([
            'status' => 'success',
            'data' => SoftwareResource::collection($data),
            'tipos' => $tipos,
            'message' => 'Software disponible recuperado correctamente'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ucoip::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if($request->id){
            $software = $this->softwareService->updateSoftware(
                $request->id, $request->empresa, $request->version, $request->licencia,
                $request->observaciones, $request->cat_software_id,
                $request->estatusm, $request->tipo_licencia, $request->cuenta,
                $request->pass_cuenta, $request->fecha_adquisicion
            );
        }else{
            $software =  $this->softwareService->storeSoftware(
                $request->empresa, $request->version, $request->licencia,
                $request->observaciones, $request->cat_software_id,
                $request->tipo_licencia, $request->cuenta, $request->pass_cuenta,
                $request->fecha_adquisicion, $request->estatus);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Software almacenado correctamente',
            'data' =>  $software
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('ucoip::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('ucoip::edit');
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
        $registro = Software::find($id);
        if($registro){
            $registro->activo = 0;
            $registro->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado correctamente',
            'data' => []
        ]);
    }

    public function getCatalogoDisponible($idEmpresa){
        $data = $this->softwareService->getCatalogoDisponible($idEmpresa);

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Software disponible recuperado correctamente'
        ]);
    }

    public function getLicenciasDisponiblesTipo($idEmpresa, $tipo){
        $data = $this->softwareService->getLicenciasDisponiblesTipo($idEmpresa, $tipo);

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Software disponible recuperado correctamente'
        ]);
    }


}
