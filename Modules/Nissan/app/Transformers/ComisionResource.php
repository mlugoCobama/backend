<?php

namespace Modules\Nissan\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Nissan\Models\Gasto;

class ComisionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $gastos = $this->getGastosFactura($this->faau_nofactura);
        return [
            'fecha_factura' => $this->fecha_factura,
            'fecha_cancelacion' => $this->fecha_cancelacion,
            'faau_vend_clave' => $this->faau_vend_clave,
            'faau_nofactura' => $this->faau_nofactura,
            'faau_razonfactura' => $this->faau_razonfactura,
            'vehi_clas_clave' => $this->vehi_clas_clave,
            'vehi_anio' => $this->vehi_anio,
            'vehi_numeroinventario' => $this->vehi_numeroinventario,
            'vehi_serie' => $this->vehi_serie,
            'mode_clave' => $this->mode_clave,
            'mode_descripcion' => $this->mode_descripcion,
            'saau_folio' => $this->saau_folio,
            'fecha_salida' => $this->fecha_salida,
            'faau_form_TipoVenta' => $this->faau_form_TipoVenta,
            'saau_vehi_vehiculoid' => $this->saau_vehi_vehiculoid,
            'faau_iva' => $this->faau_iva,
            'faau_total' => $this->faau_total,
            'Venta' => $this->Venta,
            'Costo' => $this->Costo,
            'bonificacion' => $this->bonificacion,
            'Utilidad' => $this->Utilidad,
            'otros' => $gastos['otros'],
            'gasolina'=>$gastos['gasolina'],
            'previa'=>$gastos['previa'],
            'descuentos'=>$gastos['descuentos'],
            'traslados'=>$gastos['traslados'],
            'descuento_impulso'=>$gastos['descuento_impulso'],
            'total_subsidios'=>$gastos['total_subsidios'],
            'descuento_gastos'=>$gastos['descuento_gastos'],
            'cortesia'=>$gastos['cortesia'],
            'accesorios'=>$gastos['accesorios'],
            'placas'=>$gastos['placas'],
            'isNew'=>$gastos['isNew'],
        ];
    }

    private function getGastosFactura($folioFactura){
        $gastos = Gasto::where('folio_factura',$folioFactura)->first();
        if($gastos){
            return [
            'folio_factura'=>$gastos->folio_factura ?? 0,
            'otros'=>$gastos->otros ?? 0,
            'gasolina'=>$gastos->gasolina ?? 0,
            'previa'=>$gastos->previa ?? 0,
            'descuentos'=>$gastos->descuentos ?? 0,
            'traslados'=>$gastos->traslados ?? 0,
            'descuento_impulso'=>$gastos->descuento_impulso ?? 0,
            'total_subsidios'=>$gastos->total_subsidios ?? 0,
            'descuento_gastos'=>$gastos->descuento_gastos ?? 0,
            'cortesia'=>$gastos->cortesia ?? 0,
            'accesorios'=>$gastos->accesorios ?? 0,
            'placas'=>$gastos->placas ?? 0,
            'isNew' => false
            ];
        }
        return [
            'folio_factura'=>$gastos->folio_factura ?? 0,
            'otros'=>$gastos->otros ?? 0,
            'gasolina'=>$gastos->gasolina ?? 0,
            'previa'=>$gastos->previa ?? 0,
            'descuentos'=>$gastos->descuentos ?? 0,
            'traslados'=>$gastos->traslados ?? 0,
            'descuento_impulso'=>$gastos->descuento_impulso ?? 0,
            'total_subsidios'=>$gastos->total_subsidios ?? 0,
            'descuento_gastos'=>$gastos->descuento_gastos ?? 0,
            'cortesia'=>$gastos->cortesia ?? 0,
            'accesorios'=>$gastos->accesorios ?? 0,
            'placas'=>$gastos->placas ?? 0,
            'isNew' => true
        ];
        // if($gastos != null){
        //     return [
            
        //     ];
        // }
    }
}
