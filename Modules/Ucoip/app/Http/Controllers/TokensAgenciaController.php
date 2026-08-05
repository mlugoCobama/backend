<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Enums\EstatusActivos;
use App\Enums\EstatusAsignaciones;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Ucoip\Models\CatEmpresas;
use Modules\Ucoip\Models\CatPuestosMarca;
use Modules\Ucoip\Models\TokenAgencia;
use Modules\Ucoip\Services\TokensService;

class TokensAgenciaController extends Controller
{

 public function __construct(
        private TokensService $tokenAgenciaService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $tokens =  TokenAgencia::with(['puestoMarca','sucursal'])->active()->get();
        $catalogo =  CatPuestosMarca::active()->get();
        $empresas = CatEmpresas::where('division', 4)->get();

        $data = [
            'tokens' => $tokens,
            'puestos' => $catalogo ?? [],
            'sucursales' => $empresas ?? []
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Datso recuperados correctamente'
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
        try {

            $resultado = $this->tokenAgenciaService->guardar(
                $request->id,
                $request->token,
                $request->puesto_marca,
                $request->cat_empresas_id,
                $request->observaciones
            );

            return response()->json([
                'status' => 'success',
                'message' => $resultado['message'],
                'data' => $resultado['data']
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => []
            ], 422);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $data = TokenAgencia::where('estatus', EstatusActivos::DISPONIBLE)->where('ucoip_cat_empresas_id', $id)->get();
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Hardware disponible recuperado correctamente'
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
        $registro = TokenAgencia::find($id);
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


}
