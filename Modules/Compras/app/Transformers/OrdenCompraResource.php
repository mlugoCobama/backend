<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\File;

class OrdenCompraResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {

        $lugarEntrega = $this->asignarLugarEntrega($this->entrega);

        return [
            'id' => $this->id,
            'folio_oc' => $this->folio_oc,
            'fecha' => $this->fecha,
            'observaciones' => $this->observaciones,
            'estatus' => $this->estatus,
            'cotizaciones_id' => $this->cotizaciones_id,
            'entrega' => $lugarEntrega,
            'modo_pago' => $this->modo_pago,
            'fecha_entrega' => $this->fecha_entrega,
            'surtido_solicitado' => $this->surtido_solcitado,
            'tipo_pago' => $this->modoPagoToString($this->modo_pago),
            'documentos' => DocsOrdenCompraResource::collection($this->documentos),
            'acuses_entrega' => AcuseEntregaResource::collection($this->acusesEntrega),
        ];
    }

    /**
     * Asigna un lugar de entrega en base a un id asignado
     */
    private function asignarLugarEntrega($intercompania){
        $contentE = File::get(base_path('dataEntregas.json'));
        $jsonE = json_decode(json: $contentE, associative: true);
        $dataEntrega = $jsonE[$intercompania];
        return $dataEntrega;    
    } 

    private function modoPagoToString($modoPago){
        if(!empty($modoPago)){
            return $modoPago == 1 ? 'Contado' : 'Credito';
        }

        return null;
    }
}
