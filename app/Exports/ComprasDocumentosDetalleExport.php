<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;

use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ComprasDocumentosDetalleExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithTitle,
    WithEvents,
    WithColumnWidths
{
    protected $datos;
    protected $empresa;

    public function __construct($datos, $empresa)
    {
        $this->datos = $datos;
        $this->empresa = $empresa;
    }

    public function array(): array
    {
        return array_map(function ($row) {

            return [
                'Folio'                 => $row['Folio'],
                'Folio OC'              => $row['Folio_OC'],
                'Fecha'                 => $row['Fecha'],
                'Empresa'               => $row['Empresa'],
                'Estado'                => $row['Estado'],
                'Cantidad'              => $row['Cantidad'],
                'Unidad'                => $row['Unidad'],
                'Descripción'           => $row['Descripcion'],
                'Proveedor'             => $row['proveedor'],
                'Entrega Propuesta'     => $row['FechaEntregaPrometida'],
                'Fecha Entrega'         => $row['FechaEntregaReal'],
                'Entregado'             => $row['TieneAcuseEntrega'],
                'Facturado'             => $row['TieneFacturas'],
                'Pagado'                => $row['TieneComprobantes'],
                'Complemento Pago'      => $row['TieneComplementos'],
                
            ];
        }, $this->datos->toArray());
    }

    public function headings(): array
    {
        return [
            'Folio',
            'Folio OC',
            'Fecha',
            'Empresa',
            'Estado',
            'Cantidad',
            'Unidad',
            'Descripción',
            'Proveedor',
            'Entrega Propuesta',
            'Fecha Entrega',
            'Entregado',
            'Facturado',
            'Pagado',
            'Complemento Pago',
            
        ];
    }

    public function title(): string
    {
        return $this->empresa;
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $lastRow = $sheet->getHighestRow();

                /*
                |--------------------------------------------------------------------------
                | ENCABEZADOS
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1:O1')->applyFromArray([

                    'font' => [
                        'bold' => false,
                        'color' => [
                            'rgb' => 'FFFFFF'
                        ],
                    ],

                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '1F6F5F'
                        ],
                    ],

                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => 'FFFFFF'
                            ],
                        ],
                    ],

                ]);

                /*
                |--------------------------------------------------------------------------
                | FILTROS
                |--------------------------------------------------------------------------
                */

                $sheet->setAutoFilter('A1:O1');

                /*
                |--------------------------------------------------------------------------
                | COLUMNAS EN NEGRITAS
                |--------------------------------------------------------------------------
                | A = Folio
                | C = Fecha
                | D = Empresa
                | I = Proveedor
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle("A2:A{$lastRow}")
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle("C2:C{$lastRow}")
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle("D2:D{$lastRow}")
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle("I2:I{$lastRow}")
                    ->getFont()
                    ->setBold(true);

                /*
                |--------------------------------------------------------------------------
                | COLUMNA ESTADO EN GRIS CLARO
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("E2:E{$lastRow}")
                    ->applyFromArray([

                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => 'EEEEEE'
                            ],
                        ],

                    ]);

                /*
                |--------------------------------------------------------------------------
                | CENTRAR ENCABEZADOS
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1:O1')
                    ->getAlignment()
                    ->setHorizontal(
                        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                    );

                /*
                |--------------------------------------------------------------------------
                | CONGELAR ENCABEZADOS
                |--------------------------------------------------------------------------
                */
                $sheet->freezePane('A2');
            },

        ];
    }
    public function columnWidths(): array
    {
        return [
            'H' => 50,
        ];
    }
}