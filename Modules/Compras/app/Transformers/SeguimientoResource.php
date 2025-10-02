<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\EstatusSolicitud;

class SeguimientoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {
        $evento = $this->setLabelEvento($this->event);
        $datosCambio = $this->parseCambio($this->new_values);
        $mensajeCambio = $this->generarMensaje($this->event, $this->new_values );

        return [
            'id' => $this->id,
            'idSolicitud' => $this->record_id,
            'evento' => $evento,
            'mensaje' => $mensajeCambio,
            'fecha' =>  $this->created_at
        ];
    }

    private function setLabelEvento( $evento ){
        $estados = [
            "created" => "Inicio del proceso ",
            "updated" => "Se actualizo",
        ];

        return $estados[$evento]; 
    }

    

    private function generarMensaje($evento, $values)
    {
        switch ($evento) {
            case 'created':
                return 'Se creó tu solicitud con folio: ' . ($values['folio'] ?? 'SIN FOLIO');

            case 'updated':
                $autorizaciones = [
                    'auto_gg' => 'Autorizó gerencia general',
                    'auto_admin' => 'Autorizó gerencia administrativa',
                    'auto_macro' => 'Autorizó gerencia administrativa',
                ];

                foreach ($autorizaciones as $key => $mensaje) {
                    if (!empty($values[$key])) {
                        return $mensaje;
                    }
                }

                if (isset($values['estatus'])) {
                    $labels = EstatusSolicitud::labels();
                    $label = $labels[$values['estatus']] ?? 'DESCONOCIDO';
                    return 'El estatus de tu solicitud cambió a: ' . $label;
                }

                break;
        }

        return 'No se pudo generar el mensaje para el evento especificado.';
    }

    private function parseCambio($cambio)
    {
        if (is_array($cambio)) return $cambio ?? null;
        $json = json_decode($cambio, true);
        return $json;
    }


}
