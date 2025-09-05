<?php

namespace Modules\Macro\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Compras\Transformers\CatUnidadesMedidaResource;
use Modules\Compras\Transformers\DetalleAutotanqueResource;

class DetalleAlmacenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'existencia'=>$this->existencia,
            'unidadMedida'=> $this->nombre." (".$this->abreviatura.")",
            'descripcion'=>$this->descripcion,
            'observaciones'=>$this->observaciones,
            'idAt' => $this->usuario_destino,
            'idDs' => $this->id_ds,
            'confimado' => $this->confirmado,
            'statusAlmacen' => $this->estatus_almacen,
            'ordenTrabajo' => $this->orden_trabajo,
            'autoTanque' => $this->eco_detalle." ".$this->unidad_detalle,
            'observaciones' => $this->observaciones
        ];
    }
}
