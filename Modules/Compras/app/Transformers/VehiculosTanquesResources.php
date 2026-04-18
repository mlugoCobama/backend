<?php

namespace Modules\Compras\Transformers;

use BcMath\Number;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiculosTanquesResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
         $clase = $this->asignarBadges($this->id_sucursal);
        $estilosEstatus = $this->getColorEstatus($this->estatus);
        return [
            "id" => $this->id,
            'id_cre' => $this->id_cre ?? null,
            "eco" => $this->nro_economico,
            "marca_vehiculo" => $this->marca_vehiculo,
            "submarca" => $this->submarca,
            "modelo" => $this->modelo,
            "no_serie" => $this->no_serie,
            "placas" => $this->placas,
            "placas" => $this->placas,
            "estatus" => $this->estatus,
            "tipo_combustible" => $this->tipo_combustible,
            "categoria" => $this->categoria,

            "num_tarjeta_toka" => $this->num_tarjeta_toka,
            "num_tag" => $this->num_tag,
            "limite" => $this->limite,
            "gps" => $this->gps,
            "id_tanque" => $this->id_tanque,
            "marca_tanque" => $this->marca_tanque,
            "anio_fabricacion" => $this->anio_fabricacion,
            "capacidad" => $this->capacidad,
            "serie" => $this->serie,
            "tipo_medidor" => $this->tipo_medidor,
            "tipo_vehiculo" => $this->tipo,
            "id_sucursal" => $this->id_sucursal,
            "sucursal" => $this->sucursal,
            "entidad" => $this->entidad, 
            "idSeguro" => $this->idSeguro ?? null,
            "aseguradora" => $this->aseguradora ?? null,
            "cobertura" => $this->cobertura ?? null,
            "inciso_vehiculo" => $this->inciso_vehiculo ?? null,
            "inicio_vigencia" => $this->formatFecha($this->inicio_vigencia ?? null),
            "fin_vigencia" => $this->formatFecha($this->fin_vigencia ??  null),
            "flotilla" => $this->flotilla ?? null,
            "inciso_foltilla" => $this->inciso_foltilla ?? null,
            "clase" => $clase['clase'],
            "activo" => $this->activo ?? null,
            'color_estatus' => $estilosEstatus['color'],
            'status_dsc' => $estilosEstatus['dsc'],

        ];
        
    }

    public function formatFecha($fecha_original){
        if(isset($fecha_original) && !empty($fecha_original)){
            return date("Y-m-d", strtotime(str_replace("/", "-", $fecha_original)));
        }

        return null;
    }

     private function asignarBadges($entidad)
    {
        $estados = [
            1 =>  ['clase' => 'badge bg-primary' ],
            2 =>  ['clase' => 'badge bg-secondary' ],
            3 =>  ['clase' => 'badge bg-success' ],
            5 =>  ['clase' => 'badge bg-danger' ],
            4 =>  ['clase' => 'badge bg-warning text-dark' ],
            6 =>  ['clase' => 'badge bg-info text-dark' ],
            7 =>  ['clase' => 'badge bg-light text-dark' ],
            8 =>  ['clase' => 'badge bg-dark' ],
            9 =>  ['clase' => 'badge rounded-pill bg-success' ],
            10 => ['clase' => 'badge rounded-pill bg-primary' ],
            11 => ['clase' => 'badge rounded-pill bg-warning text-dark' ],
            12 => ['clase' => 'badge text-bg-primary border border-light' ],
            13 => ['clase' => 'badge text-bg-success border border-dark' ],
            14 => ['clase' => 'badge text-bg-info border border-primary' ],
            15 => ['clase' => 'badge bg-transparent text-primary border border-primary' ],
            16 => ['clase' => 'badge bg-transparent text-success border border-success' ],
            17 => ['clase' => 'badge bg-transparent text-danger border border-danger' ],
            18 => ['clase' => 'badge badge rounded-pill bg-dark text-warning' ],
        ];

        return $estados[$entidad] ?? ['clase' => 'badge badge rounded-pill bg-secondary'];
    }

    private function getColorEstatus($estatus)
    {
        return match ($estatus) {
            "1" => ['color'=> '#28a745', 'dsc' => 'Activa'] , 
            "0" => ['color'=> '#df2727ff', 'dsc' => 'Fuera de circulación'] ,
            "2" => ['color'=> '#ffc107', 'dsc' => 'En taller'] ,
            "3" => ['color'=> '#17a2b8', 'dsc' => 'Vendida'] ,
            "4" => ['color'=> '#343a40', 'dsc' => 'No identificada'] ,
            "5" => ['color'=> '#e83e8c', 'dsc' => 'Descompuesta']   , 
            "6" => ['color'=> '#007bff', 'dsc' => 'En Fiscalía'] , 
            "7" => ['color'=> '#6610f2', 'dsc' => 'En Depósito vehicular'] ,
            "8" => ['color'=> '#495057', 'dsc' => 'Chatarra'] , 
            "9" => ['color'=> '#fd7e14', 'dsc' => 'Vendida como chatarra'] ,
            "10" => ['color'=> '#dc3545', 'dsc' => 'Baja'] , 
            default => ['color'=> '#adb5bd', 'dsc' => 'Desconocido'] ,
        };

    }


}
