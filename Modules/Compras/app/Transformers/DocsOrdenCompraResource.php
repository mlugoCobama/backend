<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocsOrdenCompraResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            "fecha"=> $this->fecha,
            "ruta_xml_factura"=> $this->ruta_xml_factura,
            "ruta_pdf_factura"=> $this->ruta_pdf_factura,
            "comprobante_pago"=> $this->comprobante_pago,
            "orden_compra_id"=> $this->orden_compra_id
        ];
    }
}
