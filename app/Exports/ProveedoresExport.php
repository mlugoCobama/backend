<?php

namespace App\Exports;

use DateInterval;
use DateTime;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Compras\Models\Proveedores;

class ProveedoresExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Proveedores::with([
            'datosPago',
            'contactos',
            'productos',
            'Expediente',
            'contactos.zona'
        ])
        ->active()
        ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'NOMBRE',
            'RFC',
            'CONTACTO',
            'TELÉFONO',
            'LOCALIDAD',
            'CONDICIONES',
            'SERVICIOS',
            'CORREO',
            'HORARIO ATENCIÓN',
            'TIEMPO ENTREGA',
            'DÍAS CRÉDITO',
            'ACTIVO',
            'ESTATUS',
            'FALTANTES',
            'DOCUMENTOS FALTANTES',
            'PRODUCTOS',
            'EXPEDIENTE ACTUALIZADO',
        ];
    }

    public function map($proveedor): array
    {
        $productos = $this->formatProductos($proveedor->productos);

        $estExpediente = $this->getStatusExpediente(
            $proveedor->Expediente
        );

        $expActualizado = $this->dentroDeTresMeses(
            $proveedor->Expediente->updated_at ?? null
        );

        return [
            $proveedor->id,
            strtoupper($proveedor->nombre),
            strtoupper($proveedor->rfc ?? 'PENDIENTE'),
            strtoupper($proveedor->contacto),
            $proveedor->telefono,
            $proveedor->localidad,
            $proveedor->condiciones,
            strtoupper($proveedor->servicios),
            strtolower($proveedor->correo),
            strtoupper($proveedor->horario_atencion),
            strtoupper($proveedor->tiempo_entrega),
            $proveedor->dias_credito,
            $proveedor->activo ? 'SI' : 'NO',
            strtoupper($estExpediente['label']),
            $estExpediente['faltantes'],
            $estExpediente['faltantes_texto'],
            strtoupper($productos),
            $expActualizado ? 'SI' : 'NO',
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
