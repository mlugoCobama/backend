<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Ucoip\Models\SistemasUcoip;
use Modules\Ucoip\Services\CifradoService;

class AsignacionSistemaController extends Controller
{

     protected $cifradoService;
    public function __construct(
        CifradoService $cifradoService,
    ){
        $this->cifradoService = $cifradoService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('ucoip::index');
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

        $data =  $request->all();
        $asignacion =  new SistemasUcoip();
        $asignacion->username =  $data['usuario'];
        $asignacion->password =  $this->cifradoService->encrypt($data['password']);
        $asignacion->ucoip_cat_sistemas_id = $data['sistema'];
        $asignacion->ucoip_ucoip_id =  $data['idUcoip'];
        $asignacion->observaciones =  $data['observaciones'];
        $asignacion->fecha_asignacion =  now();
        $asignacion->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Sistema asignado correctamente',    
            'data' => []
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $accesos =  SistemasUcoip::with(['sistema'])->where('ucoip_ucoip_id', $id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $accesos, 
            'message' => 'Datos recuperados correctamente', 
        ]);
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
        $asignacion = SistemasUcoip::find($id);
        if($asignacion){
            $asignacion->fecha_fin = now();
            $asignacion->activo = 0;
            $asignacion->save();
        }


        return response()->json([
            'status' => 'success',
            'data' => [],
            'messages' => 'Activo retirado correctamente'
        ]);
    }

    public function getPassword(int $id)
    {
        $ucoip = SistemasUcoip::findOrFail($id);
        $password = $this->cifradoService->decrypt($ucoip->password);

        return response()->json([
            'success' => true,
            'data' => $password,
            'message' => 'Dato recuperado correctamente'
        ]);
    }
}
