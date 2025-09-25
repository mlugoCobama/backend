<?php

namespace Modules\Capacitaciones\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermisosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return 
        // [
        //     'permiso' => ucfirst(
        //         strtolower(
        //             str_replace(
        //                 "ver ", "", $this->descripcion)
        //                 )
        //                 )
        // ];
        [
            'permiso' => ucfirst(
                mb_strtolower(
                    str_replace("ver ", "", $this->descripcion),
                    'UTF-8'
                )
            )
        ];

    }
}
