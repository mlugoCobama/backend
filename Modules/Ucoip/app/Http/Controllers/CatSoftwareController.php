<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Enums\EstatusActivos;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Ucoip\Models\CatSoftware;
use Modules\Ucoip\Models\Software;
use Modules\Ucoip\Transformers\SoftwareResource;

class CatSoftwareController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Software::with(['tipoSoftware', 'sucursal'])->get();
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
            $software =  Software::where('id', $request->id);
            if($software){
                $software->update([
                    "empresa" => $request->empresa,
                    "version" => $request->version,
                    "licencia" => $request->licencia,
                    "observaciones" => $request->observaciones,
                    "cat_software_id" => $request->cat_software_id,
                    "estatus" => $request->estatus,
                    "tipo_licencia" => $request->tipo_licencia,
                    "cuenta" => $request->cuenta,
                    "pass_cuenta" => $request->pass_cuenta,
                    "fecha_adquisicion" => $request->fecha_adquisicion,
                ]);
            }
        }else{
            $software = Software::create([
                "empresa" => $request->empresa,
                "version" => $request->version,
                "licencia" => $request->licencia,
                "observaciones" => $request->observaciones,
                "cat_software_id" => $request->cat_software_id,
                "tipo_licencia" => $request->tipo_licencia,
                "cuenta" => $request->cuenta,
                "pass_cuenta" => $request->pass_cuenta,
                "fecha_adquisicion" => $request->fecha_adquisicion ??  now(),
                "estatus" => $request->estatus ?? EstatusActivos::DISPONIBLE
            ]);
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
        //
    }

    public function getCatalogoDisponible($idEmpresa){
        $data = CatSoftware::with([
            'licenciasDisponible' => function ($query) use ($idEmpresa) {
                $query->where('empresa', $idEmpresa);
            }
        ])->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Software disponible recuperado correctamente'
        ]);
    }

    public function getLicenciasDisponiblesTipo($idEmpresa, $tipo){
        $data =  Software::where('estatus', EstatusActivos::DISPONIBLE)
        ->where('cat_software_id', $tipo)
        ->where('empresa', $idEmpresa)  
        ->get();
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Software disponible recuperado correctamente'
        ]);
    }

    
}
