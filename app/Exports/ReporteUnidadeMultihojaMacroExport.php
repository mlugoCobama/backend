<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteUnidadeMultihojaMacroExport implements WithMultipleSheets
{
    protected $detalle;
    public function __construct( $detalle)
    {
        $this->detalle = $detalle;
        
    }

    public function sheets(): array
    {
        $sheets = [];
        $empresas = [
            333 => 'CORPORACION ADMINISTRATIVA DEL SUR', 201 => 'AGRUPAMIENTO',
            131 => 'AZTECA GAS', 130 => 'SATELITE GAS', 251 => 'FLAMAMEX',
            210 => 'REYES GAS', 155 => 'GASAMEX', 135 => 'SEGAS', 110 => 'GARZA GAS',
            111 => 'GARZA SUR', 250 => 'GAS FLAMAZUL', 132 => 'GAS PREMIO',
            200 => 'TANQUES SONI', 119 => 'TANQUES GARZA GAS', 190 => 'ZUGAS',
            133 => 'GASERA MULTIREGIONAL', 353 => 'GAS URBANO', 710 => 'NISSAN UNIVERSIDAD',
            7051 => 'NISSAN AZCAPOTZALCO', 712 => 'NISSAN CAMPESTRE', 700 => 'CORPORATIVO AUTOS SONI',
            240 => 'SERVIGAS DEL VALLE', 2000 => 'SERVICIO EL ONCE', 7064 => 'RENAULT AZCAPOTZALCO',
            7062 => 'RENAULT ECATEPEC', 7063 => 'RENAULT VALLEJO', 7061 => 'RENAULT PACHUCA',
            191 => 'BARAGAS', 354 => 'IZTAGAS Y ENERGIA', 353111 => 'GAS URBANO - GARZA SUR', 251250 => 'FLAMAMEX - FLAMAZUL'
        ];

        // Hojas por empresa
        foreach ($this->detalle as $empresa => $rows) {
            // $sheets[$empresa] = new GastosDetalleEmpresaExport($rows, $empresa);
            $sheets[$empresa] = new GastosUnidadesDetallesExport($rows, $empresas[$empresa]);
        }

        return $sheets;
    }
}
