<?php

namespace Modules\Ucoip\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SoftwareResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {
       return [ 
            "id" => $this->id,
            "empresa" => $this->empresa,
            'sucursal' => $this->sucursal,
            "cat_software_id" => $this->cat_software_id,
            "version" => $this->version,
            "licencia" => $this->licencia,
            "observaciones" => $this->observaciones,
            "usuario_empresa_id" => $this->usuario_empresa_id,
            "activo" => $this->activo,
            "estatus" => $this->estatus,
            "tipo_licencia" => $this->tipo_licencia,
            "cuenta" => $this->cuenta,
            "pass_cuenta" => $this->pass_cuenta,
            "fecha_adquisicion" => $this->fecha_adquisicion,
            "tipo_software" =>  $this->tipoSoftware,
            "tipo_texto" => $this->setTipo( $this->tipo_licencia),
            "fecha" => $this->fecha ? $this->fecha->format('d/m/y') : '-',
        ];
        
    }

    private function setTipo($tipo){

    return match ($tipo) {
        '1' => 'Suscripción',
        '2' => 'Perpetua',
        '3' => 'Por Volumen',
        '4' => 'Libre',
        default => 'No definido'
    };

    }
}
