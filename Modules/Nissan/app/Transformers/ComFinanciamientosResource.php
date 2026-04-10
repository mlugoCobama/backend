<?php

namespace Modules\Nissan\Transformers;

use App\Enums\EstatusComisionesAutos;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComFinanciamientosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
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
            "agencia" => $this->agencia,

            // "created_at" => $this->created_at,
            // "updated_at" => $this->updated_at,
            "estatus" => $this->estatus,
            "comentario" => $this->comentario,
            "estatusTexto" => EstatusComisionesAutos::label($this->estatus),
            "activo" => $this->activo,
            "no_vendedor" => $this->vendedor->nro_vendedor_as,
            "vendedor" => ($this->vendedor->nombre ?? 'No disponible') ?? 'Desconocido',
            'vehiculo' =>  $this->venta ?  $this->venta->descripcion : 'No Disponible',
            'cliente' =>  $this->venta ?  $this->venta->razon_social : 'No Disponible',
            'serie' =>  $this->venta ?  $this->venta->serie : 'No Disponible',

            
            "kit_seguridad" => $this->kit_seguridad ?? 0,
            "sat_finder" => $this->sat_finder ?? 0,
            "garantia_extendida" => $this->garantia_extendida ?? 0,
            "seguro_vf3" => $this->seguro_vf3 ?? 0,
            "accesorios_adicionales" => $this->accesorios_adicionales ?? 0,
            "comision_mantenimiento" => $this->comision_mantenimiento ?? 0,
            "comision_garantia_extendida" => $this->comision_garantia_ext ?? 0,
            "comision_udi" => $this->comision_udi ?? 0,
            "comision_vf3" => $this->comision_vf3 ?? 0,
            "sub_x_des" => $this->sub_x_des ?? 0,
        ];
    }
}
