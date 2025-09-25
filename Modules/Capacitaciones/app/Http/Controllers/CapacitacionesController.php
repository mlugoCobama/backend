<?php

namespace Modules\Capacitaciones\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Capacitaciones\Models\CatalogoFuncionesAs;
use Modules\Capacitaciones\Models\CatalogoModulosAs;
use Modules\Capacitaciones\Models\CatalogoSubmodulosAs;
use Modules\Capacitaciones\Models\ModulosSubmodulos;
use Modules\Capacitaciones\Transformers\FuncionesAsResource;
use Modules\Capacitaciones\Transformers\ModulosSubmodulosResources;
use Modules\Capacitaciones\Transformers\SubmodulosAsResource;

class CapacitacionesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $modulo = CatalogoModulosAs::where('nombre', "bancos")->first();
        $submodulo = new SubmodulosAsResource(CatalogoSubmodulosAs::where('nombre', 'procesos')->where('cat_modulos_as_id', $modulo->id)->first());
        return response()->json([
            'status' => 'success',
            'data' => $submodulo,
            'message' => 'datos recuperados correctamente'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('capacitaciones::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        
        return view('capacitaciones::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('capacitaciones::edit');
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

    public function getFunciones($nModulo, $nSubmodulo ){
        $modulo = CatalogoModulosAs::where('nombre', $nModulo)->first();
        $submodulo = CatalogoSubmodulosAs::where('nombre', $nSubmodulo)->first();

        $funciones = ModulosSubmodulos::with('funciones')
        ->where([
            ['catalogo_modulos_as_id', '=', $modulo->id],
            ['catalogos_submodulos_as_id', '=', $submodulo->id]
        ])
        ->get();
  
            $funcionesFormateadas = $funciones[0]->funciones->map(function ($funcion) use ($modulo, $submodulo) {
                $funcion->ruta_video = "http://192.168.22.226:8080/capacitacion_as/" .
                    urlencode(str_replace(' ', '-', $modulo->nombre)) . "/" .
                    urlencode(str_replace(' ', '-', $submodulo->nombre)) . "/" .
                    urlencode(str_replace(' ', '-', $funcion->ruta_video)) . ".mp4";
                return $funcion;
            });

        return response()->json([
            'status' => 'success',
            'data' => $funcionesFormateadas,
            'message' => 'datos recuperados correctamente'
        ]);
    }
}
