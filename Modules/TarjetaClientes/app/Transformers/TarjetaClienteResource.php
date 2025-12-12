<?php

namespace Modules\TarjetaClientes\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TarjetaClienteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'agencia'           => $this->agencia,
            'folio'             => $this->folio ??  'Sin Folio',
            'no_sicop'             => $this->no_sicop ??  'Sin Numero',
            'asesor_ventas'     => $this->asesor_ventas,
            'nombre_cliente'    => $this->nombre_cliente,
            'direccion'         => $this->direccion,
            'ciudad'            => $this->ciudad,
            'estado'            => $this->estado,
            'email_personal'    => $this->email_personal,
            'email_trabajo'    => $this->email_trabajo ?? 'No especifico',
            'telefono_principal'=> $this->telefono_principal,
            'telefono_secundario'    => $this->telefono_secundario ?? 'No especifico',
            'telefono_adicional'    => $this->telefono_adicional ?? 'No especifico',

            'tiene_cita'        => $this->tiene_cita == "1" ? "Sí" : "No",

            'tipo_contacto'     => $this->tipo_contacto,
            'tipo_contacto_texto' => $this->mapTipoContacto($this->tipo_contacto),
            'cual_publicidad'     => $this->cual_publicidad,

            'servicio'          => $this->servicio,
            'servicio_texto' => $this->mapTipoServicio($this->servicio),
            'notas_apv'          => $this->notas_apv,
            'notas_gv'          => $this->notas_gv,

            'cliente_quiere'    => $this->cliente_quiere == '1' ? 'Nuevo' : 'Semi nuevo',
            'anio'              => $this->anio,
            'modelo'            => $this->modelo,
            'estilo'            => $this->estilo,
            'color'             => $this->color,
            'stock_vin'         => $this->stock_vin,
            'equipo_particular' => $this->equipo_particular,


            'anio_vehiculo'     => $this->anio_vehiculo,
            'modelo_vehiculo'   => $this->modelo_vehiculo,
            'estilo_vehiculo'   => $this->estilo_vehiculo,
            'color_vehiculo'    => $this->color_vehiculo,
            'ac'                => $this->ac ? "Con aire acondicionado" : "Sin aire acondicionado",
            'pw'                => $this->pw,
            'pl'                => $this->pl,
            'cruise'            => $this->cruise,
            'tilt'              => $this->tilt,
            'auto_1'              => $this->auto,
            'x4x4'              => $this->x4x4,
            'cd'                => $this->cd,
            'sat'               => $this->sat,
            'navi'              => $this->navi,
            'kilometraje'       => $this->kilometraje ?? 0,
            'vin'               => $this->vin,
            'costo_pagar'       => $this->costo_pagar ?? 0,
            'acv'               => $this->acv,
            'telefono_banco'    => $this->telefono_banco,
            'auto'              => $this->auto ? "Automático" : "Manual",
            'created_at'        => $this->created_at->format('d/m/Y H:i'),
        ];
    }

    private function mapTipoContacto($tipo)
    {
        $map = [
            "1" => "Cliente",
            "2" => "Digital",
            "3" => "Recom",
            "4" => "Llamó",
            "5" => "Seg.",
            "6" => "P.R.-Otro",
            "7" => "Regreso",
            "8" => "Visita #1",
        ];

        return $map[$tipo] ?? "Desconocido";
    }

    private function mapTipoServicio($tipo)
    {
        $map = [
            "1" => "Presen",
            "2" => "Demo",
            "3" => "Camino",
            "4" => "Escribió",
            "5" => "T/O.",
            "6" => "Compró",
        ];

        return $map[$tipo] ?? "Desconocido";
    }

}
