<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class GastosVehiculoExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
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

    /**
     * Orden de las columnas
     */
    public function map($row): array
    {
        return [
            $row->solicitud_compra,
            $row->orden_compra,
            $row->cantidad,
            $row->abreviatura,
            $row->descripcion,
            $row->importe_unitario,
            $row->total,
            $row->iva,
            $row->total_detalle,
        ];
    }

    /**
     * Encabezados
     */
    public function headings(): array
    {
        return [
            'SOLICITUD',
            'ORDEN',
            'CANTIDAD',
            'TIPO',
            'DESCRIPCIÓN',
            'IMPORTE U.',
            'SUB TOTAL',
            'IVA',
            'TOTAL',
        ];
    }

    /**
     * Estilos
     */
    public function styles(Worksheet $sheet)
    {
        // Encabezados
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => [
                    'rgb' => 'FFFFFF'
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '0B3D91' // Azul marino
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Bordes para toda la tabla
        $ultimaFila = $sheet->getHighestRow();

        $sheet->getStyle("A1:I{$ultimaFila}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Formato moneda
        $sheet->getStyle("F2:I{$ultimaFila}")
            ->getNumberFormat()
            ->setFormatCode('$#,##0.00');

        // Centrar columnas
        $sheet->getStyle("A:I")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A:D")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
