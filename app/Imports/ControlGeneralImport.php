<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithLimit;

class ControlGeneralImport implements Import, ToCollection, WithStartRow, WithLimit
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

    public function collection(Collection $rows):void
    {
        $row = $rows->first();
        if ($row) {
            $this->data = [
                'ClaveInstalacion'                  => (string)($row[0] ?? ''),
                'ExistenciasMesInmediatoAnterior'   => (float)($row[10] ?? 0),
            ];
        }
    }
}

