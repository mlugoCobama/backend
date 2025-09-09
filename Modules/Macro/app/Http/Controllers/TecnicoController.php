<?php

namespace Modules\Macro\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Macro\Models\Tecnico;
use Illuminate\Support\Facades\DB;
use Modules\Macro\Transformers\TecnicoResource;
use PhpParser\Node\Stmt\Return_;

class TecnicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $empresas = DB::connection('intranet')->table('glpi_entities')->select('name','intercompania')
        ->where('intercompania', '>', '0')
        ->where('intercompania', '<', '700')
        ->get();
        $data = Tecnico::active()->get();

        $resultado = $data->map(function ($tecnico) use ($empresas) {
            // Buscamos la empresa que coincide con el intercompania del técnico
            $empresa = $empresas->firstWhere('intercompania', $tecnico->intercompania);

            // Agregamos la información de la empresa al técnico
            return [
                'tecnico' => $tecnico,
                'empresa' => $empresa ? $empresa->name : null,
            ];
        });



        return response()->json([
            'status' => 'success',
            'data' => TecnicoResource::collection($resultado),
            'empresas' => $empresas,
            'message' => 'datos recuperados correctamente'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('macro::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        try {
            if(!empty($data['id'])){
                $this->updateTecnico($data);
                $operacion = 'actualizado';
            }else{

                $this->storeTecnico($data);
                $operacion = 'creado';
            }
            return response()->json([
                'status' => 'success',
                'data' => $request->all(),
                'message' => "Técnico $operacion correctamente",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar proveedor',
                'error' => $e->getMessage()
            ]);
        }

        
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('macro::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('macro::edit');
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
        $tecnico = Tecnico::where('id', $id);
        
        if(!$tecnico){
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas eliminar no existe',
                'data' => ''
            ]);
        }

        $tecnico->update([
            'activo' => 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => ''
        ]);
    }

    public function storeTecnico($data){
        $dataTecnico = new Tecnico();
        $dataTecnico->nombre = $data["nombre"];
        $dataTecnico->apellidos = $data["apellidos"];
        $dataTecnico->tipo = $data["tipo"];
        $dataTecnico->intercompania = $data["empresa"];
        $dataTecnico->save();
    }

    public function updateTecnico($data){

        $proveedor = Tecnico::find($data['id']);
        if (!$proveedor) {
            throw new \Exception("Proveedor no encontrado");
        }

        $proveedor->update([
            'nombre' => $data["nombre"],
            'apellidos' => $data["apellidos"],
            'tipo' => $data["tipo"],
            'intercompania' => $data["empresa"],
        ]);
    }
}
