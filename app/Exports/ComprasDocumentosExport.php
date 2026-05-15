<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ComprasDocumentosExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles
{
    protected $datos;

    public function __construct(Collection $datos)
    {
        $this->datos = $datos;
    }

    public function collection()
    {
        return $this->datos->map(function ($row) {

            return [
                'Folio'                 => $row['Folio'],
                'Folio OC'              => $row['Folio_OC'],
                'Fecha'                 => $row['Fecha'],
                'Empresa'               => $row['Empresa'],
                'Destino'               => $row['Destino'],
                'Área'                  => $row['Area'],
                'Estado'                => $row['Estado'],

                'Cantidad'              => $row['Cantidad'],
                'Descripción'           => $row['Descripcion'],
                'Observaciones'         => $row['Observaciones'],
                'Unidad'                => $row['Unidad'],
                'Precio'                => $row['Precio'],

                'Tipo'                  => $row['tipo'],
                'Proveedor'             => $row['proveedor'],

                'Tiene Facturas'        => $row['TieneFacturas'],
                'Tiene Complementos'    => $row['TieneComplementos'],
                'Tiene Comprobantes'    => $row['TieneComprobantes'],

                'Total Facturado'       => $row['TotalFacturado'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Folio',
            'Folio OC',
            'Fecha',
            'Empresa',
            'Destino',
            'Área',
            'Estado',

            'Cantidad',
            'Descripción',
            'Observaciones',
            'Unidad',
            'Precio',

            'Tipo',
            'Proveedor',

            'Tiene Facturas',
            'Tiene Complementos',
            'Tiene Comprobantes',

            'Total Facturado',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [

            // Encabezados
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],

        ];
    }
}