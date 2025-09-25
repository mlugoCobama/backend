<?php

namespace Modules\Capacitaciones\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Capacitaciones\Models\UsuarioPuesto;
use Modules\Capacitaciones\Transformers\UsuarioPuestoResource;

class AdministracionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = UsuarioPuestoResource::collection(UsuarioPuesto::active()->with('puesto')->get());

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Puesto asignado correctamente'
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
    public function store(Request $request)
    {
        $data = $request->all();

        if (!empty($data['id'])) {
            $usuarioPuesto = UsuarioPuesto::find($data['id']);
            if (!$usuarioPuesto) {
                throw new \Exception("Usuario-Puesto no encontrado");
            }

            $usuarioPuesto->update([
                'id_usuario' => (int)$data['usuario'],
                'id_puesto' => (int)$data['puesto'],
            ]);
        } else {
            $puesto = new UsuarioPuesto();
            $puesto->id_usuario = (int)$data['usuario'];
            $puesto->id_puesto = (int)$data['puesto'];
            $puesto->save();
        }


        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'Puesto asignado correctamente'
        ]);
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
       $puestoUsuario = UsuarioPuesto::where('id', $id);
        
        if(!$puestoUsuario){
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas eliminar no existe',
                'data' => ''
            ]);
        }

        $puestoUsuario->update([
            'activo' => 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => ''
        ]);
    }
}
