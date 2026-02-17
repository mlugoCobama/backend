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

class ReporteSemanalCambiosCompras implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithColumnFormatting,  WithColumnWidths, WithStyles
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
            'Fecha',                // A-0
            'Folio',                // B-1
            'Folio OC',             // C-2
            'Estatus',              // D-3
            'Ultimo Cambio',        // E-4
            'Cantidad',             // F-5
            'Unidad',               // G-6
            'Descripción',          // H-7
            'Observaciones',        // I-8
            'Precio',               // K-9
            'Subtotal',             // L-10
            'IVA',                  // M-11
            'Total',                // N-12
            'Proveedor',            // O-13
            'AT',                   // P-16
            'Tipo Mantenimiento',   // Q-14
            'Sistema',              // R-15           
        ];
    }

    public function array(): array
{
    // Convertimos la colección a array
    $data = array_map(function ($row) {

        return [
            $row['Fecha'],
            $row['Folio'],
            $row['Folio_OC'],
            $row['Estado'],
            $row['Modificado'],
            $row['Cantidad'],
            $row['Unidad'],
            $row['Descripcion'],
            $row['Observaciones'],
            $row['Precio'],
            $row['Subtotal'],
            $row['IVA'],
            $row['Total'],
            $row['Proveedor'],
            $row['Destino'],
            $row['tipoMantenimiento'],
            $row['sistemaMantenieminto'],
            
        ];

    }, $this->detalle->toArray());

    return $data;
}

    public function title(): string
    {
        return $this->empresa;
    }

    public function columnFormats(): array
    {
        return [
            'J' => '"$ "* #,##0.00_-',
            'K' => '"$ "* #,##0.00_-',
            'L' => '"$ "* #,##0.00_-',
            'M' => '"$ "* #,##0.00_-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'H' => 70,
            'I' => 70,
            'N' => 70 
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('D')->getFont()->setBold(true); 
        $sheet->getStyle('M')->getFont()->setBold(true); 
        return [];
    }
}
