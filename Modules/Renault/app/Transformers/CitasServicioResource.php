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
            'id' => $this->id,
            'folio' => $this->folio,
            'clave_empl' => $this->empleado_id,
            'nombre_empl' => $this->nombre,
            'fecha_cita' => $this->fecha,
            'nombre' => $this->nombre,
            'apaterno' => $this->apellido_paterno,
            'amaterno' => $this->apellido_materno,
            'rfc' => $this->rfc,
            'telefono' => $this->telefono,
            'domicilio' => $this->domicilio,
            'email' => $this->email,
            'vin' => $this->vin,
            'placas' => $this->placas,
            'modelo' => $this->modelo,
            'tipo' => $this->tipo,
            'kilometraje' => $this->kilometraje,
            'observaciones' => $this->observaciones,
            'color' => $this->color,
            'anio' => $this->anio,
            'estatus' => $this->estatus,
            'tipoCita' => $this->tipo_cita
        ];
    }
}
