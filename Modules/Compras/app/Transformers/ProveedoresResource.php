<?php

namespace Modules\Compras\Transformers;

use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Compras\Models\ProveedorContacto;

class ProveedoresResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {
        $productos = $this->formatProductos($this->productos);
        $estExpediente = $this->getStatusExpediente($this->Expediente);
        $expActualizado = $this->dentroDeTresMeses($this->Expediente->updated_at ?? null);
        // return parent::toArray($request);
        return[
            'id'=>$this->id,
            'nombre'=> strtoupper($this->nombre) ,
            'rfc'=> strtoupper($this->rfc ?? 'PENDIENTE'),
            'contacto'=> strtoupper($this->contacto),
            'telefono'=> $this->telefono,
            'localidad'=>$this->localidad,
            'condiciones'=> $this->condiciones,
            'servicios'=> strtoupper($this->servicios),
            'correo'=> strtolower($this->correo),
            'horario_atencion'=>strtoupper($this->horario_atencion),
            'tiempo_entrega'=> strtoupper($this->tiempo_entrega),
            'dias_credito'=>$this->dias_credito,
            'activo'=> $this->activo,
            'expediente'=>  new ExpedientesProveedoresResource($this->Expediente),
            'contactos' => ProveedorContactosResource::collection($this->contactos),
            'datosPago' => DatosPagoProveedorResource::collection($this->datosPago),
            'estatus' => strtoupper($estExpediente['label']),
            'faltantes' => $estExpediente['faltantes'],
            'documentos_faltantes_texto' => $estExpediente['faltantes_texto'],
            'productos' => strtoupper($productos),
            'expActualizado' => $expActualizado
        ];
    }

    /**
     * Formatea la colección de productos en una cadena de texto legible
     * 
     * Extrae los nombres de todos los productos asociados al proveedor
     * y los concatena en una sola cadena separada por comas
     * @param  $productos Colección de productos del proveedor
     * @return string|null Cadena con nombres de productos separados por comas, o null si no hay productos                
     */
    private function formatProductos($productos){
        $data = json_decode(json_encode($productos), true);
        if(is_array($data) && !empty($data)){
            $nombres = array_column($data, 'nombre');
            $cadena = implode(', ', $nombres);
            return $cadena;
        }   
        return null;
    }
    /**
     * Analiza el estado de completitud del expediente del proveedor
     * @param $expediente Modelo del expediente
     * @return array Array con el estado del expediente:
     *               - 'label' 
     *               - 'faltantes' 
     *               - 'faltantes_texto'
     */

    private function getStatusExpediente($expediente)
    {
        if (!empty($expediente)) {
            $nombresLegibles = [
                'constancia_fiscal' => 'Constancia de situación fiscal',
                'ine' => 'INE',
                'comprobante_domicilio' => 'Comprobante de domicilio',
                'estado_cuenta' => 'Estado de cuenta',
                'acta_constitutiva' => 'Acta constitutiva',
                'poder_notarial' => 'Poder notarial',
                'opinion_cumplimiento' => 'Opinión de cumplimiento',
                'contrato' => 'Contrato'
            ];

            $faltantes = collect($expediente->toArray())
                ->filter(fn($valor) => is_null($valor))
                ->keys()
                ->filter(fn($campo) => array_key_exists($campo, $nombresLegibles))
                ->values();

            $nombresFaltantes = $faltantes->map(fn($campo) => $nombresLegibles[$campo])->all();
            $totalFaltantes = count($nombresFaltantes);

            if ($totalFaltantes > 0) {
                return [
                    'label' => "Faltan",
                    'faltantes' => $totalFaltantes,
                    'faltantes_texto' => implode(', ', $nombresFaltantes)
                ];
            }

            return [
                'label' => 'Completo',
                'faltantes' => 0,
                'faltantes_texto' => ''
            ];
        }

        return [
            'label' => 'No tiene',
            'faltantes' => 0,
            'faltantes_texto' => ''
        ];
    }

    private function dentroDeTresMeses($fecha)
    {
        if(!empty($fecha)){
            $fechaReferencia = new DateTime($fecha);
            $fechaLimite = $fechaReferencia->add(new DateInterval('P3M'));
            $hoy = new DateTime();

            return $hoy < $fechaLimite;
        }
        
        return false;
    }


}
