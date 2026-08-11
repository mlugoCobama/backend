<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;

class ContribuyenteImport implements ToCollection, WithHeadingRow, WithLimit
{
    public array $data = [];

    public function limit(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        $row = $rows->first();
        if ($row) {
            $this->data = [
                'Nombre'                => (string)($row['nombre'] ?? '1.0'),
                'Version'               => (string)($row['version'] ?? '1.0'),
                'RfcContribuyente'      => (string)($row['rfccontribuyente'] ?? ''),
                'RfcRepresentanteLegal' => (string)($row['rfcrepresentantelegal'] ?? ''),
                'RfcProveedor'          => (string)($row['rfcproveedor'] ?? '')
            ];
        }
    }
}
