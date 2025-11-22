<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class GastosDetalleEmpresaExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithColumnFormatting

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
            // 'ID Solicitud',
            // 'Estatus',
            // 'Intercompañía',
            'Folio Solicitud',
            'Fecha',
            'Folio OC',
            'Proveedor',
            'Servicios',
            'Total'
        ];
    }

    public function array(): array
    {
         $data = array_map(function ($row) {
            return [
                // $row->idSolicitudCompra,
                // $row->estatus,
                // $row->intercompania,
                $row->folio_solicitud,
                $row->fecha,
                $row->folio_orden_compra,
                $row->proveedor,
                $row->servicios,
                $row->total_por_folio
            ];
        }, $this->detalle);


        $totalGeneral = array_sum(array_column($data, 5));

        $data[] = [
            // '',                ID Solicitud
            // '',                Estatus
            // '',                Intercompañía
            '',                // Folio Solicitud
            '',                // Fecha
            '',                // Folio OC
            '',                // Proveedor
            'TOTAL GENERAL',   // Servicios
            $totalGeneral      // Total por Folio
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
            'F' => '"$ "* #,##0.00_-'
        ];
    }

}
