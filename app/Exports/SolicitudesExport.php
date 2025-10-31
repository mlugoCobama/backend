<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

use Maatwebsite\Excel\Excel;



class SolicitudesExport implements FromCollection, WithHeadings
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

    public function headings(): array
    {
        return [
            // 'ID Solicitud',
             'Folio',
             'Fecha',
              'Empresa',
               'Estado',
                'Detalles JSON'];
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
