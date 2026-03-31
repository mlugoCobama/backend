<?php

namespace Modules\Nissan\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComFinanciamientosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $estatusTexto = $this->setEstatusTexto($this->estatus);
        return [
            "id" => $this->id,
            "no_contrato" => $this->no_contrato,
            "fecha_desembolso" => $this->fecha_desembolso,
            "numero_factura" => $this->numero_factura,
            "monto_financiar" => $this->monto_financiar,
            "incentivo_dealer" => $this->incentivo_dealer,
            "porcentaje_asesor" => $this->porcentaje_asesor,
            "comision_asesor_pesos" =>$this->comision_asesor_pesos,
            "com_vendedores_id" => $this->com_vendedores_id,
            "tipo_financiamiento" => $this->tipo_financiamiento,
            "com_datos_venta_id" => $this->com_datos_venta_id,
            "ruta_archivo" => $this->ruta_archivo,
            "observaciones" => $this->observaciones,

            // "created_at" => $this->created_at,
            // "updated_at" => $this->updated_at,
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
