<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Subtotal;

class SolicitudesExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithColumnWidths, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $solicitudes;

    public function __construct($solicitudes)
    {
        $this->solicitudes = $solicitudes;
    }

    public function collection()
    {
        return collect($this->solicitudes);
    }
     public function title(): string
    {
        return 'Solicitudes de compra';
    }

    public function columnWidths(): array
    {
        return [
            'G' => 70,
            'H' => 70,
            'I' => 70,
        ];
    }

    public function headings(): array
    {
        return [
            'Folio',            //A-0
            'Fecha',            //B-1
            'Empresa',          //C-2
            'Estado',           //D-3
            'Cantidad',         //E-4
            'Unidad',           //F-5
            'Descripción',      //G-6
            'Observaciones',    //H-7
            
            'Proveedor',        //I-8
            'Tipo de mantenimiento',        //I-8
            'Sistema',        //I-8
            // 'Precio',
            // 'Subtotal',
            // 'IVA',
            // 'Total'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A')->getFont()->setBold(true); 
        $sheet->getStyle('C')->getFont()->setBold(true); 

        return [];
    }

    // public function registerEvents(): array
    // {
    //     return [
    //         \Maatwebsite\Excel\Events\BeforeExport::class => function(\Maatwebsite\Excel\Events\BeforeExport $event) {
    //             $event->writer->setOutputEncoding('UTF-8');
    //             $event->writer->getProperties()->setCreator('Sistema de Compras');
    //         },
    //     ];
    // }



}
