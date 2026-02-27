<?php

namespace Modules\Renault\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DatosEntradaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empleado_id' => $this->empleado_id,
            'telefono' => $this->telefono,

            'id_entrada' => $this->Datos->id,
            'fecha_entrada' => $this->Datos->fecha,
            'num_entrada' => $this->Datos->num_entrada,

            'id_inventario' => $this->Datos->inventario->id,
            'antena' => $this->Datos->inventario->antena,
            'antena' => $this->Datos->inventario->antena,
            'espejo' => $this->Datos->inventario->espejo,
            'tapones' => $this->Datos->inventario->tapones,
            'rines' => $this->Datos->inventario->rines,
            'tapon_gasolina' => $this->Datos->inventario->tapon_gasolina,
            'radio' => $this->Datos->inventario->radio,
            'encendedor' => $this->Datos->inventario->encendedor,
            'tapetes' => $this->Datos->inventario->tapetes,
            'llanta_refaccion' => $this->Datos->inventario->llanta_refaccion,
            'herramientas' => $this->Datos->inventario->herramientas,
            'reflejantes' => $this->Datos->inventario->reflejantes,
            'extinguidor' => $this->Datos->inventario->extinguidor,
            'gato' => $this->Datos->inventario->gato,
            'objetos_valor' => $this->Datos->inventario->objetos_valor,
            'otros' => $this->Datos->inventario->otros,
            'vestiduras' => $this->Datos->inventario->vestiduras,
            'cristales' => $this->Datos->inventario->cristales,
            'testigos_fotograficos' => TestigosFotograficosResource::collection($this->Datos->TestigosFotograficos)

        ];
    }
}
