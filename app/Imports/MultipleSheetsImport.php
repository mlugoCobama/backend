<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class MultipleSheetsImport implements WithMultipleSheets
{
    protected array $sheetsData = [];

    public function sheets(): array
    {

        return [
            0 => new DynamicSheetImport(),
            1 => new DynamicSheetImport(),
        ];
    }
}
