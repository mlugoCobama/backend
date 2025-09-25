<?php

namespace Modules\Capacitaciones\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ModulosAsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'permiso' => $this->permiso,
            // 'idPermiso' => $this->getIdPermiso($this->permiso),
            'submodulos' => $this->ModulosSubmodulos->map(function ($modSub) {
                return [
                    'id' => $modSub->Submodulo->id,
                    'nombre' => $modSub->Submodulo->nombre,
                    'permiso' => $modSub->permiso,
                    // 'idPermiso' => $this->getIdPermiso($modSub->permiso),
                    'funciones' => $modSub->funciones->map(function ($funcion) {
                        return [
                            'id' => $funcion->id,
                            'nombre' => $funcion->nombre,
                            'ruta_video' => $funcion->ruta_video,
                            'catalogos_submodulos_as_id' => $funcion->modulo_submodulos_as_id,
                            'permiso' => $funcion->permiso,
                            // 'idPermiso' => $this->getIdPermiso($funcion->permiso),
                            'activo' => $funcion->coincide ?? false
                        ];
                    }),
                    'activo' => $modSub->coincide ?? false
                ];
            }),
            'activo' => $this->coincide ?? false
        ];
    }

    public function getIdPermiso($nombrePermiso){
        $permiso = DB::table('permissions')->where('name', $nombrePermiso)->first('id');
        return $permiso->id ?? $permiso;
    }
}
