<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithTitle;

class EasyGasExport implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                'id_cliente'      => $item->id_cliente,
                'nomina'          => $item->nomina,
                'monto_deseado'   => $item->monto_deseado ?? 0,
                'producto'        => 'EASYGAS DIESEL CHIP',
                'observaciones'   => $item->observaciones ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID CLIENTE',
            'NOMINA',
            'MONTO DESEADO',
            'PRODUCTO',
            'OBSERVACIONES',
        ];
    }

    public function title(): string
    {
        return 'Sheet1';
    }
}