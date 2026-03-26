<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteConcentradoComprasDetalleExport implements FromArray, WithHeadings, WithTitle,WithColumnWidths, WithColumnFormatting
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
            'Estatus',          // B-0    
            'Folio OC',         // C-1
            'Fecha',            // D-2
            'Cantidad',         // E-3
            'Unidad',           // F-4
            'Descripción',      // G-5
            'Observaciones',    // H-6
            'Precio',           // I-7
            'Destino',          // J-8
            'Area',             // K-9
            'Categoria',        // L-10
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
            $row['Estado'],
            $row['Folio_OC'],
            $row['Fecha'],
            $row['Cantidad'],
            $row['Unidad'],
            $row['Descripcion'],
            $row['Observaciones'],
            $row['Precio'],
            $row['Destino'],
            $row['Area'],
            $row['tipo'],
        ];

    }, $this->detalle->toArray());


    // // Cálculos de totales
    // $subTotalGeneral = array_sum(array_column($data, 9));
    // $totalIva        = array_sum(array_column($data, 10));
    // $totalGeneral    = array_sum(array_column($data, 11));

    // // Fila de totales
    // $data[] = [
    //     '', '', '', '', '', '', '', '', 
    //     'TOTAL GENERAL',
    //     $subTotalGeneral,
    //     $totalIva,
    //     $totalGeneral
    // ];

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
         ];
     }

    public function columnWidths(): array
    {
        return [
            'B' => 30,
            'D' => 30,
            'F' => 30,
            'G' => 70,
            'H' => 70, 
            'I' => 10, 
            'J' => 70,
            'K' => 70,
            'L' => 70,

        ];
    }

    // public function styles(Worksheet $sheet)
    // {
    //     $sheet->getStyle('L')->getFont()->setBold(true); 

    //     return [];
    // }
}
