<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class GastosConcentradoExport implements FromArray, WithHeadings, WithTitle,  ShouldAutoSize, WithColumnFormatting
{
    protected $concentrado;

    public function __construct($concentrado)
    {
        $this->concentrado = $concentrado;
    }

    public function headings(): array
    {
        return [
            // 'Intercompañía',
            'Empresa',
            'Solicitudes',
            'Total'
        ];
    }

    public function array(): array
    {

        $data = array_map(function ($row) {
            return [
                // $row->num_intercompania,
                $row->empresa,
                $row->solicitudes,
                $row->total_por_empresa
            ];
        }, $this->concentrado);

        $totalGeneral = array_sum(array_column($data, 2)); 

        $data[] = [
            // '', Intercompañía
            'TOTAL GENERAL', // Empresa
            '', // Solicitudes
            $totalGeneral // Total por Empresa
        ];

        return $data;
    }

    public function title(): string
    {
        return 'Concentrado';
    }

    public function columnFormats(): array
{
    return [
        'C' => '"$ "* #,##0.00_-'
    ];
}
}
