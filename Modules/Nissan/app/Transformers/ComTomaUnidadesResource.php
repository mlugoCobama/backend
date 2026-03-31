<?php

namespace Modules\Nissan\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComTomaUnidadesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $estatusTexto = $this->setEstatusTexto($this->estatus);
        return [
            "id" => $this->id,
            "no_inventario" =>$this->no_inventario,
            "clave_inventario" =>strtoupper($this->clave_producto).'-'.$this->anio.'-'.$this->no_inventario,
            "clave_producto" =>$this->clave_producto,
            "anio" =>$this->anio,
            "fecha_toma" =>$this->fecha_toma,
            "comision_apv_pesos" =>$this->comision_apv_pesos,
            "com_vendedores_id" => $this->com_vendedores_id,
            "com_datos_venta_id" => $this->com_datos_venta_id,
            "observaciones" => $this->observaciones,
            "estatus" => $this->estatus,
            "comentario" => $this->comentario,
            "estatusTexto" => $estatusTexto,
            "activo" => $this->activo,
            "vendedor" => $this->vendedor->nro_vendedor_as.' '.($this->vendedor->nombre ?? 'No disponible') ?? 'Desconocido',
        ];
    }

    public function setEstatusTexto($estatus){
        return match ($estatus) {
            1 => 'Por Autorizar',
            2 => 'Autorizada',
            3 => 'Pagada',
            4 => 'Rechazada',
            default => 'Desconocido'
        };
    }
}
