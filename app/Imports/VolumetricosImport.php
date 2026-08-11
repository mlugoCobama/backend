<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class VolumetricosImport implements WithMultipleSheets
{
    public ContribuyenteImport $generalImport;
    public PermisosImport $permisosImport;
    public ControlGeneralImport $controlGeneralImport;
    public array $recepcionImports = [];
    public array $entregaImports   = [];

    public function __construct()
    {
        $this->generalImport  = new ContribuyenteImport();
        $this->permisosImport =  new PermisosImport();
        $this->controlGeneralImport =  new ControlGeneralImport();



        for ($i = 1; $i <= 5; $i++) {
            $this->recepcionImports[$i] = new MovimientoImport();
            $this->entregaImports[$i]   = new MovimientoImport();
        }
    }

    public function sheets(): array
    {
        return [
            'Datos Contribuyente' => $this->generalImport,
            'Permisos'            => $this->permisosImport,
            'Recpciones Nacionales Mismo Mes' => $this->recepcionImports[1],
            'Recpciones Extranjero Mismo Mes' => $this->recepcionImports[2],
            'Recepciones Costo $0.0' => $this->recepcionImports[3],
            'Recepcs Compra Anticpada +1 mes' => $this->recepcionImports[4],
            'Recepcs Compra Crédito +1 mes' => $this->recepcionImports[5],

            'Entregas Mismo Mes CDFI Indvdl'   => $this->entregaImports[1],
            'Entregas Costo $0.0'   => $this->entregaImports[2],
            'Entregas Mismo Mes CFDI Global'   => $this->entregaImports[3],
            'Entregas Crédito +1 mes'   => $this->entregaImports[4],
            'Entregas Compra Antcipda +1 mes'   => $this->entregaImports[5],
            'Control General' => $this->controlGeneralImport
        ];
    }

    /**
     * Consolida los registros de las 4 hojas de Recepción en un solo arreglo
     */
    public function getTodasLasRecepciones(): array
    {
        $consolidado = [];
        foreach ($this->recepcionImports as $import) {
            $consolidado = array_merge($consolidado, $import->data);
        }
        return $consolidado;
    }

    /**
     * Consolida los registros de las 4 hojas de Entrega en un solo arreglo
     */
    public function getTodasLasEntregas(): array
    {
        $consolidado = [];
        foreach ($this->entregaImports as $import) {
            $consolidado = array_merge($consolidado, $import->data);
        }
        return $consolidado;
    }
}
