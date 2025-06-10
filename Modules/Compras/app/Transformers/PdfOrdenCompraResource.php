<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Support\Facades\File;


class PdfOrdenCompraResource extends JsonResource
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
            'cotizacion' => new CotizacionesResource($this->cotizacion),
            'entrega' => $lugarEntrega,
            'documentos' => DocsOrdenCompraResource::collection($this->documentos),

        ];
            
    }
    private function asignarLugarEntrega($intercompania){
        $contentE = File::get(base_path('dataEntregas.json'));
        $jsonE = json_decode(json: $contentE, associative: true);
        $dataEntrega = $jsonE[$intercompania];
        return $dataEntrega;    
    } 
}
