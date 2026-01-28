<?php

namespace Modules\Nissan\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Nissan\Models\Vendedor;

class DatosVentaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
                "id"=> $this->id,
                "fecha_as_salida"=> date('d/m/Y', strtotime($this->fecha_as_salida)),
                "no_factura"=> $this->no_factura,
                "razon_social"=> $this->razon_social,
                "descripcion"=> $this->descripcion,

                "no_inventario"=> $this->no_inventario,
                "clave_inventario" => $this->clave_producto.'-'.$this->anio_vehiculo.'-'. $this->no_inventario,
                'id_vendedor' => $this->id_vendedor,

                'nro_vendedor_as' => $this->vendedor->nro_vendedor_as,
                'vendedor_agencia' => $this->vendedor->agencia,
                'porcentaje_vendedor' => $this->vendedor->porcentaje_apv,
                'clave_vendedor' => $this->vendedor->nro_vendedor_as,
                
                "serie"=> $this->serie,
                "total_venta"=> $this->total_venta,
                "costos"=>$this->costos,
                "bonificaciones"=>$this->bonificaciones,
                "utilidad_inicial"=> $this->utilidad_inicial,
                
                "estatus"=>$this->estatus,
                "entregado"=>$this->entregado == 0 ? false : true,
                "bdc"=>$this->bdc,
                "agencia"=>$this->agencia,
                "validado"=>$this->validado,
                "clave_producto"=>$this->clave_producto,
                "modelo_producto"=>$this->modelo_producto,
                "anio_vehiculo"=>$this->anio_vehiculo,

                "tipo_venta"=>$this->tipo_venta,
                'tipo_venta_id' => $this->tipo_venta_id,
                "tipo_venta_nombre" => $this->tipoVenta->nombre,
                "tipo_venta_porcentaje" => $this->tipoVenta->porcentaje,

                "fecha_factura"=> date('d/m/Y', strtotime($this->fecha_factura)),
                "pagado"=> $this->pagado,
                "observacion"=> $this->observacion,

                'gastos' => [
                    'id' => $this->gatosVenta->id ?? null,
                    'otros' => (float) ($this->gatosVenta->otros ?? 0),
                    'gasolina' => (float) ($this->gatosVenta->gasolina ?? 0),
                    'previa' => (float) ($this->gatosVenta->previa ?? 0),
                    'descuentos' => (float) ($this->gatosVenta->descuentos ?? 0),
                    'descuento_impulso' => (float) ($this->gatosVenta->descuento_impulso ?? 0),
                    'traslados' => (float) ($this->gatosVenta->traslados ?? 0),
                    'subsidios' => (float) ($this->gatosVenta->subsidios ?? 0),
                    'descuento_da' => (float) ($this->gatosVenta->descuento_da ?? 0),
                    'cortesia' => (float) ($this->gatosVenta->cortesia ?? 0),
                    'accesorios' => (float) ($this->gatosVenta->accesorios ?? 0),
                    'placas' => (float) ($this->gatosVenta->placas ?? 0),
                    'comision_apv_pesos' => (float) ($this->gatosVenta->comision_apv_pesos ?? 0),
                    'comision_bdc_pesos' => (float) ($this->gatosVenta->comision_bdc_pesos ?? 0),
                
            ],
            ];
    }
}
