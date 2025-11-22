<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GatosDetalleEmpresaDetalladoExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithColumnFormatting,  WithColumnWidths, WithStyles
{

    protected $detalle;
    protected $empresa;

    public function __construct($detalle, $empresa)
    {
        $this->detalle = $detalle;
        $this->empresa = $empresa;

    }

    public function headings(): array
    {
        return [
            'Folio',            // A-0
            'Folio OC',         // B-1
            'Fecha',            // C-2
            'Cantidad',         // D-3
            'Unidad',           // E-4
            'Descripción',      // F-5
            'Observaciones',    // G-6
            'Proveedor',        // H-7
            'Precio',           // I-8
            'Subtotal',         // J-9
            'IVA',              // K-10
            'Total',             // L-11
            'AT'
        ];
    }

    // /**
    // * @return \Illuminate\Support\Collection
    // */
    // public function collection()
    // {
    //     //
    // }

 public function array(): array
{
    // Convertimos la colección a array
    $data = array_map(function ($row) {

        return [
            $row['Folio'],
            $row['Folio_OC'],
            $row['Fecha'],
            $row['Cantidad'],
            $row['Unidad'],
            $row['Descripcion'],
            $row['Observaciones'],
            $row['Proveedor'],
            $row['Precio'],
            $row['Subtotal'],
            $row['IVA'],
            $row['Total'],
            $row['Destino'],
        ];

    }, $this->detalle->toArray());


    // Cálculos de totales
    $subTotalGeneral = array_sum(array_column($data, 9));
    $totalIva        = array_sum(array_column($data, 10));
    $totalGeneral    = array_sum(array_column($data, 11));

    // Fila de totales
    $data[] = [
        '', '', '', '', '', '', '', '', 
        'TOTAL GENERAL',
        $subTotalGeneral,
        $totalIva,
        $totalGeneral
    ];

    return $data;
}

    public function title(): string
    {
        return $this->empresa;
    }

    public function columnFormats(): array
    {
        return [
            'I' => '"$ "* #,##0.00_-',
            'J' => '"$ "* #,##0.00_-',
            'K' => '"$ "* #,##0.00_-',
            'L' => '"$ "* #,##0.00_-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'F' => 70,
            'G' => 70,
            'H' => 70 
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('L')->getFont()->setBold(true); 

        return [];
    }
}
