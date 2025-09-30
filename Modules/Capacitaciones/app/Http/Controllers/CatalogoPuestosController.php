<?php

namespace Modules\Capacitaciones\Http\Controllers;

use App\Http\Controllers\Controller;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Capacitaciones\Models\CatalogoModulosAs;
use Modules\Capacitaciones\Models\CatalogoPuestos;
use Modules\Capacitaciones\Models\PermissionHasPuesto;
use Modules\Capacitaciones\Transformers\ModulosAsResource;
use Modules\Capacitaciones\Transformers\PermisosResource;

class CatalogoPuestosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data =  CatalogoPuestos::active()->orderBy('nombre')->get();
        
        //$catModulos = CatalogoModulosAs::with([
        //     'ModulosSubmodulos.Submodulo',
        //     'ModulosSubmodulos.funciones'
        // ])->get();



        return response()->json([
            'status' =>  'success',
            'data' => $data,
            // 'catalogo' => $catModulos,
            'message' => 'Puestos recuperados correctamente'
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
        DB::beginTransaction();

        try {
            $data = $request->all();
            $idPuesto = $this->storePuesto($data['puesto']);
            $permisosPuesto = $data['permisos'];
            $this->storePermisos($idPuesto, $permisosPuesto);

            DB::commit();

            return response()->json([
                "status" => 'success',
                'data' => [],
                'message' => "Puesto y permisos generados correctamente"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "status" => 'error',
                'message' => "Error al generar el puesto y permisos",
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {

        $permisos_as = DB::table('permissions as p')
            ->join('permission_has_puesto as pp', 'p.id', '=', 'pp.permiso_id')
            ->join('cap_cataologo_puestos as cp', 'cp.id', '=', 'pp.puesto_id')
            ->select('p.descripcion')
                ->where('cp.id', '=', $id)
            ->get();



        return response()->json([
            "status" => 'success',    
            "data" => PermisosResource::collection($permisos_as),
            "message" => "Permisos recuperados correctamente"
            ]);

        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $permisos_as = DB::table('permissions as p')
            ->join('permission_has_puesto as pp', 'p.id', '=', 'pp.permiso_id')
            ->join('cap_cataologo_puestos as cp', 'cp.id', '=', 'pp.puesto_id')
            ->select('p.name')
            ->where('cp.id', '=', $id)
            ->pluck('p.name');

        $catModulos = CatalogoModulosAs::with([
            'ModulosSubmodulos.Submodulo',
            'ModulosSubmodulos.funciones'
        ])->get();

        $Modulos = $catModulos->map(function ($modulo) use ($permisos_as) {
            $modulo->coincide = $permisos_as->contains($modulo->permiso);

            $modulo->ModulosSubmodulos = collect($modulo->ModulosSubmodulos)->map(function ($submodulo) use ($permisos_as) {
                $submodulo->coincide = $permisos_as->contains($submodulo->permiso);

                $submodulo->funciones = collect($submodulo->funciones)->map(function ($funcion) use ($permisos_as) {
                    $funcion->coincide = $permisos_as->contains($funcion->permiso);
                    return $funcion;
                });

                return $submodulo;
            });

            return $modulo;
        });

        return response()->json([
            "status" => 'success',
            "data" => ModulosAsResource::collection($Modulos),
            "Message" => "Datos para editar recuperados correctamente"

        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $idPuesto = $id;
            $permisosPuesto = $data['permisos'];

            $clean = $this->deletePermisos($id);

            if ($clean) {
                $this->storePermisos($idPuesto, $permisosPuesto);
            } else {
                throw new \Exception("No se pudieron eliminar los permisos anteriores.");
            }

            DB::commit();

            return response()->json([
                "status" => 'success',
                'data' => [],
                'message' => "Puesto y permisos actualizados correctamente"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "status" => 'error',
                'message' => "Error al actualizar el puesto y permisos",
                'error' => $e->getMessage()
            ]);
        }


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $puesto = CatalogoPuestos::where('id', $id);
        
        if(!$puesto){
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas eliminar no existe',
                'data' => ''
            ]);
        }

        $puesto->update([
            'activo' => 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => ''
        ]);
    }


    public function storePuesto($nPuesto){
        $puesto = new CatalogoPuestos();
        $puesto->nombre = $nPuesto;
        $puesto->save();
        return $puesto->id;
    }


    public function storePermisos($idPuesto, $permisos){

        foreach ($permisos as $permiso) {
            $id = $this->getIdPermiso($permiso['permiso']);
            $permiso = new PermissionHasPuesto();
            $permiso->permiso_id = $id;
            $permiso->puesto_id = $idPuesto;
            $permiso->save();
        }
    }

    public function getIdPermiso($nombrePermiso){
        $permiso = DB::table('permissions')->where('name', $nombrePermiso)->first('id');
        return $permiso->id;
    }

    public function deletePermisos($id){

        PermissionHasPuesto::where('puesto_id', $id)->delete();

        return true;
        // $permisos = PermissionHasPuesto::where('puesto_id', $id)->get();

        // foreach ($permisos as $permiso) {
        //     $permiso->delete();
        // }


    }
}
