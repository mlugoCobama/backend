<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class WorkflowExport implements 
    FromCollection, 
    WithHeadings, 
    WithStyles, 
    ShouldAutoSize,
    WithEvents,
    WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = collect($data);
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Folio',
            'Ultimo Estatus',
            'Fecha Alta',
            'Esp.Autorización de planta',
            'Solicitado a compras',
            'Cotización',
            'Dias de cambio de solicitado a cotizacion',
            'Orden Compra',
            'Dias de cambio de cotizacion a OC',
            'En Surtido',
            'Dias de cambio de OC a Surtido',
            'Facturado',
            'Dias de cambio de OC a Facturado',
            'Solicitado a Pago',
            'Dias de cambio de Surtido a Solicitado a pago',
            'Pagado',
            'Dias de cambio de Solicitado a pago a Pagado ',
            'Complemento de pago',
            'Dias de cambio de Pago a Complemento de pago ',
            'Finalizado',
            'Tiempo Total de Solicitud',
            'Cacelacion',
            'Tiempo hasta cancelacion',
            'Entrega',
            'Tiempo de entrega',
            
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],
            ],
        ];
    }

    public function registerEvents1(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $sheet->freezePane('A2');

                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                // Header color
                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '404040'],
                    ],
                ]);

                // Altura encabezado
                $sheet->getRowDimension(1)->setRowHeight(50);

                // Alineación
                $sheet->getStyle("A2:A{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal('left');

                $sheet->getStyle("B2:{$lastColumn}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal('center');

                // Wrap
                // $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                //     ->getAlignment()
                //     ->setWrapText(true);
                $sheet->getStyle("A1:{$lastColumn}1")
                    ->getAlignment()
                    ->setWrapText(true);
                        },
        ];
    }

    public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {

            $sheet = $event->sheet->getDelegate();

            $sheet->freezePane('A2');

            $lastColumn = $sheet->getHighestColumn();
            $lastRow = $sheet->getHighestRow();

            // Header color
            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '404040'], 
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],
            ]);

            // Altura encabezado
            $sheet->getRowDimension(1)->setRowHeight(45);

            // Alineación
            $sheet->getStyle("A2:A{$lastRow}")
                ->getAlignment()
                ->setHorizontal('left');

            $sheet->getStyle("B2:{$lastColumn}{$lastRow}")
                ->getAlignment()
                ->setHorizontal('center');

            // Wrap + centrado vertical
            $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                ->getAlignment()
                ->setWrapText(true)
                ->setVertical('center');

            // Bordes tipo tabla
            $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle('thin');

            // Autofiltro
            $sheet->setAutoFilter("A1:{$lastColumn}1");

            // COLOREAR COLUMNAS "Dias"
            $lastColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastColumn);

            for ($col = 1; $col <= $lastColumnIndex; $col++) {

                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);

                $header = $sheet->getCell("{$columnLetter}1")->getValue();

                if (
                    stripos($header, 'Dias') !== false || // contiene "Dias"
                    stripos($header, 'Esp') === 0 ||     // inicia con "Esp"
                    stripos($header, 'Tiempo') === 0     // inicia con "Tiempo"
                    ) {

                    $sheet->getStyle("{$columnLetter}2:{$columnLetter}{$lastRow}")
                        ->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'EDEDED'], // gris claro
                            ],
                        ]);
                }
            }
        },
    ];
}

    public function columnWidths(): array
{
    return [
        'A' => 20, 
        'B' => 25, 
        'C' => 22,
        'D' => 20, 
        'E' => 20,
        'F' => 20,
        'G' => 20,
        'H' => 20,
        'I' => 20,
        'J' => 20,
        'K' => 20,
        'L' => 20,
        'M' => 20,
        'N' => 20,
        'O' => 20,
        'P' => 20,
        'Q' => 20,
        'R' => 20,
        'S' => 20,
        'T' => 20,
        'U' => 20,
        'V' => 20,
        'W' => 20,
    ];
}
}