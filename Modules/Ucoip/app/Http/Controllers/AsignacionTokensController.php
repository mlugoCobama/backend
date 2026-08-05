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
use Modules\Ucoip\Services\TokensService;

class AsignacionTokensController extends Controller
{

    protected $tokenService;
    public function __construct(
        TokensService $tokenService,
    ){
        $this->tokenService = $tokenService;
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
        $asignacion =  $this->tokenService->asiganarToken($data['ucoip_ucoip_id'], $data['token'],$data['usuario'],$data['acceso'],$data['contrasenia'], now());


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
        $asignacion = $this->tokenService->finalizarAsignacionToken($id);

        return response()->json([
            'status' => 'success',
            'data' => [],
            'messages' => 'Activo retirado correctamente'
        ]);
    }

    public function getPassword(int $id, $campo)
    {
        $ucoip = TokensUcoip::findOrFail($id);
        if($campo == 'acc'){
            $word = $ucoip->acceso;
        }else{
            $word = $ucoip->contrasenia;
        }


        $password = $this->tokenService->descifrarPassword($word);

        return response()->json([
            'success' => true,
            'data' => $password,
            'message' => 'Dato recuperado correctamente'
        ]);
    }
}
