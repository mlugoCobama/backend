<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use setasign\Fpdi\Fpdi;

class CompraSeminuevosPDFController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function checklistPDF($data)
    {
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->setSourceFile(__DIR__."/../../../../../storage/app/modules/nissan/compra_seminuevos/checklist_seminuevos.pdf");

        $template = $pdf->importPage(1);
        $pdf->useImportedPage($template);

        $pdf->SetFont('Helvetica','',8);
        $pdf->SetTextColor(0,0,0);

        $pdf->SetXY(38, 17);
        $pdf->Write(0, $data->dataVehiculo->factura_ampara );
        $pdf->SetXY(75, 17);
        $pdf->Write(0, $data->nombre . ' ' . $data->apellido_paterno . ' ' . $data->apellido_materno );

        $pdf->SetXY(38, 22);
        $pdf->Write(0, $data->dataVehiculo->anio_modelo );
        $pdf->SetXY(75, 22);
        $pdf->Write(0, $data->dataVehiculo->tipo_version );
        $pdf->SetXY(146, 22);
        $pdf->Write(0, $data->dataVehiculo->num_serie );

        $pdf->SetXY(75, 26);
        $pdf->Write(0, "$ ".number_format($data->dataVehiculo->valor_compra,2) );
        $pdf->SetXY(146, 26);
        $pdf->Write(0, $data->dataVehiculo->tipo_version );

        $pdf->SetXY(75, 30.5);
        $pdf->Write(0, "$ ".number_format($data->dataVehiculo->valor_guia_autometria,2) );

        $pdf->SetXY(75, 34.5);
        $pdf->Write(0, "$ ".number_format( ( $data->dataVehiculo->valor_guia_autometria - $data->dataVehiculo->valor_compra ),2) );

        $pdf->Output();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function solicitudDFDI($data)
    {
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->setSourceFile(__DIR__."/../../../../../storage/app/modules/nissan/compra_seminuevos/solicitud_cfdi_seminuevos.pdf");

        $template = $pdf->importPage(1);
        $pdf->useImportedPage($template);

        $pdf->SetFont('Helvetica','',8);
        $pdf->SetTextColor(0,0,0);


        $pdf->SetXY(75, 38);
        $pdf->Write(0, $data->nombre . ' ' . $data->apellido_paterno . ' ' . $data->apellido_materno );
        $pdf->SetXY(165, 38);
        $pdf->Write(0, $data->dataCFDI->rfc );

        $pdf->SetXY(80, 57);
        $pdf->Write(0, $data->dataVehiculo->marca );
        $pdf->SetXY(163, 57);
        $pdf->Write(0, $data->dataVehiculo->anio_modelo );

        $pdf->SetXY(72,61.5);
        $pdf->Write(0, $data->dataVehiculo->num_serie);
        $pdf->SetXY(156,61.5);
        $pdf->Write(0, $data->dataVehiculo->num_motor );

        $pdf->SetXY(47, 93.5);
        $pdf->Write(0, $data->nombre );
        $pdf->SetXY(145, 93.5);
        $pdf->Write(0, $data->calle );

        $pdf->SetXY(47, 98);
        $pdf->Write(0, $data->apellido_paterno );
        $pdf->SetXY(145, 98);
        $pdf->Write(0, $data->num_interior . ' ' . $data->num_exterior );

        $pdf->SetXY(47, 102.5);
        $pdf->Write(0, $data->apellido_materno );
        $pdf->SetXY(145, 102.5);
        $pdf->Write(0, $data->colonia );

        $pdf->SetXY(47, 107);
        $pdf->Write(0, $data->dataCFDI->fecha_nacimiento );
        $pdf->SetXY(145, 107);
        $pdf->Write(0, $data->cp );

        $pdf->SetXY(47, 111.5);
        $pdf->Write(0, $data->dataCFDI->curp );
        $pdf->SetXY(145, 111.5);
        $pdf->Write(0, $data->delegacion_municipio );

        $pdf->SetXY(47, 115);
        $pdf->Write(0, $data->dataCFDI->actividad_preponderante );
        $pdf->SetXY(145, 115);
        $pdf->Write(0, $data->estado );

        $pdf->SetXY(145, 120);
        $pdf->Write(0, $data->dataCFDI->pais );

        $pdf->SetXY(47, 124);
        $pdf->Write(0, $data->dataCFDI->regimen_fiscal );
        $pdf->SetXY(145, 124);
        $pdf->Write(0, $data->dataCFDI->correo_electronico );

        $pdf->SetXY(47, 128.5);
        $pdf->Write(0, $data->dataCFDI->rfc );
        $pdf->SetXY(145, 128.5);
        $pdf->Write(0, $data->tipo_identificacion );

        $pdf->SetXY(47, 128.5);
        $pdf->Write(0, $data->dataCFDI->rfc );
        $pdf->SetXY(145, 128.5);
        $pdf->Write(0, $data->tipo_identificacion );


        $pdf->Output();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function AutenticacionDctosAnexo($data)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function CartaNoRentencionPFAEmpresas($data)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function CartaNoRentencionPF($data)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function CartaResponsiva($data)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function ContratoCompraVenta($data)
    {
        //
    }

    public function AvisoPrivacidad($data)
    {
        //
    }

    public function DacionPago($data)
    {
        //
    }

    public function SolicitudCheque($data)
    {
        //
    }

    public function CalculoRetencion($data)
    {
        //
    }

    public function RET126LISR($data)
    {
        //
    }

    public function RET3152($data)
    {
        //
    }
}
