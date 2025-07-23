<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Exception;
use Illuminate\Support\Facades\DB;

class PermisosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $permisos = DB::table('ucoip_modulos as uc')
            ->join('permissions as p', 'uc.id', '=', 'p.ucoip_modulo_id')
            ->select('uc.id', 'uc.nombre', 'p.descripcion', 'p.name', 'p.id as permiso_id')
            ->get();

            return $this->successResponse($permisos);

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

            DB::table('permissions')->insert([
                'name' => $request->descripcion,
                'guard_name' => 'web',
                'descripcion' => $request->nombre,
                'ucoip_modulo_id' => $request->modulo
            ]);
    
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
        try {
            
            $permisos = DB::table('ucoip_modulos as uc')
            ->join('permissions as p', 'uc.id', '=', 'p.ucoip_modulo_id')
            ->select('uc.id', 'uc.nombre', 'p.descripcion', 'p.name', 'p.id as permiso_id')
            ->get();

            $agrupado = [];

            foreach ($permisos as $item) {
                $clave = $item->nombre; // Agrupar por índice 4
                $agrupado[$clave][] = $item;
            }

            return $this->successResponse($agrupado);

        } catch (Exception $e) {

            return $this->errorResponse('Error al obtener los registros.');
            //Log::error('Error al obtener productos: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //

        return response()->json([]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //

        return response()->json([]);
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
