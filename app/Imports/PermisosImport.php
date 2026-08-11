<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithLimit;

class PermisosImport implements ToCollection, WithStartRow, WithLimit
{
    public array $data = [];

    public function startRow(): int
    {
        return 3;
    }

    /**
     *
     */
    public function limit(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        $row = $rows->first();
        if ($row) {
            $this->data = [
                'ClaveInstalacion'      => (string)($row[0] ?? ''),
                'Caracter'              => (string)($row[1] ?? ''),
                'ModalidadPermiso'      => (string)($row[2] ?? ''),
                'DescripcionInstalacion'=> (string)($row[3] ?? ''),
                'NumPermiso'            => (string)($row[4] ?? ''),
                'Geolocalizacion'       => [
                                                'GeolocalizacionLatitud' => (float)($row[5] ?? 0),
                                                'GeolocalizacionLongitud' => (float)($row[6] ?? 0),
                                            ],
                'FechaYHoraReporteMes'  => (string)($row[7] ?? ''),
                'ClaveProducto'  => (string)($row[8] ?? ''),
                'ComposDePropanoEnGasLP' => (float)($row[9] ?? 0),
                'ComposDeButanoEnGasLP' =>(float)($row[10] ?? 0),
                'NumeroTanques'         => (int)($row[11] ?? 0),
                'NumeroPozos'           => (int)($row[12] ?? 0),
                'NumeroDuctosEntradaSalida'            => (int)($row[13] ?? 0),
                'NumeroDuctosTransporteDistribucion' => (int)($row[14] ?? 0),
                'NumeroDispensarios'    => (int)($row[15] ?? 0),


            ];
        }
    }
}
