<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Enums\EstatusActivos;
use App\Enums\EstatusAsignaciones;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Ucoip\Models\TokenAgencia;
use Modules\Ucoip\Models\TokensUcoip;
use Modules\Ucoip\Services\CifradoService;

class AsignacionTokensController extends Controller
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
        $asignacion =  new TokensUcoip();

        $asignacion->ucoip_ucoip_id =  $data['ucoip_ucoip_id'];
        $asignacion->ucoip_token_agencias_id =  $data['token'];
        $asignacion->usuario =  $data['usuario'];

        $asignacion->acceso =  $this->cifradoService->encrypt($data['acceso']);
        $asignacion->contrasenia =  $this->cifradoService->encrypt($data['contrasenia']);
        $asignacion->fecha_asignacion =  now();
        $asignacion->save();

        $this->updateStatusToken($asignacion->ucoip_token_agencias_id, EstatusActivos::ASIGNADA);

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
        $accesos =  TokensUcoip::with(['token'])->where('ucoip_ucoip_id', $id)->get();

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
        $asignacion = TokensUcoip::find($id);
        if($asignacion){
            $asignacion->fecha_retiro = now();
            $asignacion->estatus = EstatusAsignaciones::INACTIVA;
            $asignacion->save();
        }

        $this->updateStatusToken($asignacion->ucoip_token_agencias_id, EstatusActivos::DISPONIBLE);

        return response()->json([
            'status' => 'success',
            'data' => [],
            'messages' => 'Activo retirado correctamente'
        ]);
    }

        public function updateStatusToken($id, $status){
        $software = TokenAgencia::find($id);

        if($software){
            $software->estatus = $status;
            $software->save();
        }
    }

    public function getPassword(int $id, $campo)
    {
        $ucoip = TokensUcoip::findOrFail($id);
        if($campo == 'acc'){
            $word = $ucoip->acceso;
        }else{
            $word = $ucoip->contrasenia;
        }

        
        $password = $this->cifradoService->decrypt($word);

        return response()->json([
            'success' => true,
            'data' => $password,
            'message' => 'Dato recuperado correctamente'
        ]);
    }
}
