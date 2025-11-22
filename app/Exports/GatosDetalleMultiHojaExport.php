<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GatosDetalleMultiHojaExport implements WithMultipleSheets
{

    protected $concentrado;
    protected $detalle;
    protected $empresa;

    public function __construct($concentrado, $detalle, $empresa)
    {
        $this->concentrado = $concentrado;
        $this->detalle = $detalle;
        $this->empresa = $empresa;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Hoja 1 — Concentrado general
        $sheets['Concentrado'] = new GastosDetalleEmpresaExport($this->concentrado, "RESUMEN $this->empresa");

        $sheets['Detalles'] = new GatosDetalleEmpresaDetalladoExport($this->detalle, "DETALLE $this->empresa");

        return $sheets;
    }
}
