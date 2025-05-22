<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NissanMesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'planta' => $this->planta,
            'entidad' => $this->estacion,
            'estacion' => $this->estacion,
            'fecha' => $this->fecha,
            'uno' => $this->uno,
            'gasto' => $this->gasto,
            'personal' => $this->personal,
            'cnuevos' => $this->cnuevos,
            'cflotillas' => $this->cflotillas,
            'refacciones' => $this->refacciones,
            'bajio' => $this->bajio,
            'intercias' => $this->intercias,
            'plan_piso' => $this->plan_piso,
            'plan_piso_interes' => $this->plan_piso_interes,
            'nrf' => $this->nrf,
            'nrf_interes' => $this->nrf_interes,
            'servicio' => $this->servicio,
            'utilidad_servicio' => $this->utilidad_servicio,
            'hyp' => $this->hyp,
            'utilidad_hyp' => $this->utilidad_hyp,
            'nuevos' => $this->nuevos,
            'utilidad_nuevos' => $this->utilidad_nuevos,
            'flotillas' => $this->flotillas,
            'utilidad_flotillas' => $this->utilidad_flotillas,
            'seminuevos' => $this->seminuevos,
            'utilidad_seminuevos' => $this->utilidad_seminuevos,
            'objetivo' => $this->objetivo,
            'cumplimiento' => $this->cumplimiento,
            'porcentaje' => $this->porcentaje,
            'bono_marca' => $this->bono_marca,
            'bonos' => $this->bonos,
            'incentivos' => $this->incentivos,
            'otros' => $this->otros,
            'descuentos' => $this->descuentos,
            'area_comercial' => $this->area_comercial,
            'area_postventa' => $this->area_postventa,
            'ventas_servicio' => $this->ventas_servicio,
            'total_ventas_ref' => $this->total_ventas_ref,
            'refacciones_servicio' => $this->refacciones_servicio,
            'refacciones_hyp' => $this->refacciones_hyp,
            'refacciones_mostrador' => $this->refacciones_mostrador,
            'inventario_nuevos' => $this->inventario_nuevos,
            'inventario_seminuevos' => $this->inventario_seminuevos,
            'inventario_refacciones' => $this->inventario_refacciones,
            'inv_nuevo_101' => $this->inv_nuevo_101,
            'inv_nuevo_201' => $this->inv_nuevo_201,
            'inv_nuevo_301' => $this->inv_nuevo_301,
            'inv_nuevo_401' => $this->inv_nuevo_401,
            'inv_semi_101' => $this->inv_semi_101,
            'inv_semi_201' => $this->inv_semi_201,
            'inv_semi_301' => $this->inv_semi_301,
            'inv_semi_401' => $this->inv_semi_401,
            'personal_ventas' => $this->personal_ventas,
            'personal_usados' => $this->personal_usados,
            'personal_refacciones' => $this->personal_refacciones,
            'personal_servicios' => $this->personal_servicios,
            'personal_admin' => $this->personal_admin,
            'personal_apvs' => $this->personal_apvs,
        ];
    }
}
