<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Spatie\Permission\Models\Permission;
/**
 * Models
 */
use Modules\Ucoip\Models\Modulos;
use Modules\Ucoip\Transformers\ModulosResource;

class ModulosController extends Controller
{
    private $modulos;

    public function __construct(Modulos $modulos) {
        $this->modulos = $modulos;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $modulos = ModulosResource::collection($this->modulos->active()->get());
    
            return $this->successResponse($modulos);

        } catch (Exception $e) {

            return $this->errorResponse('Error al obtener los registros.');
            //Log::error('Error al obtener productos: ' . $e->getMessage());
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $this->modulos->create([
                "nombre" => $request->nombre,
                "descripcion" => $request->descripcion,
            ]);

            Permission::create(['name' => 'view '.strtolower($request->nombre)]);
    
            return $this->successResponse([], 'Registro creado correctamente.');

        } catch (Exception $e) {

            return $this->errorResponse('Error al crear el registro: '.$e);

        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        //

        return response()->json([]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {

            $this->modulos::where('id', $id)
                ->update([
                    "nombre" => $request->nombre,
                    "descripcion" => $request->descripcion,
                ]);
    
            return $this->successResponse([], 'Registro actualizado correctamente.');

        } catch (Exception $e) {

            return $this->errorResponse('Error al actualizar el registro.');

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

            $this->modulos::where('id', $id)
                ->update(['activo' => 0 ]);
    
            return $this->successResponse([], 'Registro eliminado correctamente.');

        } catch (Exception $e) {

            return $this->errorResponse('Error al eliminar el registro.');
        }
    }

    /**
     * Helper para respuestas exitosas.
     */
    private function successResponse($data = [], $message = '')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], 200);
    }

    /**
     * Helper para respuestas con error.
     */
    private function errorResponse($message, $code = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => []
        ], $code);
    }

}
