<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GastosMultiHojaExport implements WithMultipleSheets
{
    protected $concentrado;
    protected $detalle;
    
    public function __construct($concentrado, $detalle)
    {
        $this->concentrado = $concentrado;
        $this->detalle = $detalle;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Hoja 1 — Concentrado general
        $sheets['Concentrado'] = new GastosConcentradoExport($this->concentrado);

        // Hojas por empresa
        foreach ($this->detalle as $empresa => $rows) {
            // $sheets[$empresa] = new GastosDetalleEmpresaExport($rows, $empresa);
            $sheets[$empresa] = new GatosDetalleEmpresaDetalladoExport($rows, $empresa);
        }

        return $sheets;
    }

    
}
