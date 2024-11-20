<?php

namespace Modules\Renault\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CitasServicioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'folio' => $this->citas_folio,
            'clave_empl' => $this->citas_empl_clave,
            'nombre_empl' => $this->empl_nombre,
            'fecha_cita' => $this->citas_fechacita,
            'nombre' => $this->citas_nombre,
            'apaterno' => $this->citas_apaterno,
            'amaterno' => $this->citas_amaterno,
            'rfc' => $this->citas_RFC,
            'telefono' => $this->citas_TelefonoContacto,
            'domicilio' => $this->citas_Domicilio,
            'email' => $this->citas_email,
            'vin' => $this->citas_NoSerie,
            'placas' => $this->citas_placas,
            'modelo' => $this->citas_modelo,
            'tipo' => $this->citas_tipo,
            'kilometraje' => $this->citas_Kilometraje,
            'observaciones' => $this->citas_observaciones,
            'color' => $this->citas_Color1,
            'anio' => $this->citas_AnioModelo,
            'estatus' => $this->citas_status,
            'tipoCita' => $this->citas_TipoCita
        ];
    }
}
