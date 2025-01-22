<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use setasign\Fpdi\Fpdi;

class OrdenCompraPdfController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('compras::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('compras::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('compras::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('compras::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function OrdenCompraFormatoInterno($data)
    {

        $pdf = new Fpdi();
        $pdf->AddPage();

        $pdf->setSourceFile(__DIR__ . "/../../../../../storage/app/modules/compras/orden_compra/formato_interno_orden_compra.pdf");

        $template = $pdf->importPage(1);
        $pdf->useImportedPage($template);


        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(167, 20);
        $pdf->Write(0, $data['ordenCompra']['folio_oc']);

        $pdf->SetFont('Arial', 'B', 5);
        $pdf->SetXY(25, 28);
        $pdf->Write(0, ': (Sistemas)');
        $pdf->SetXY(65, 28);
        $pdf->Write(0, $data['ordenCompra']['id']);
        $pdf->SetXY(90, 28.25);
        $pdf->Write(0, 'ELVIRA AVILA LUA');

        $fechaOriginal = $data['ordenCompra']['fecha'];
        $fecha = date("d/m/Y", strtotime($fechaOriginal));
        $pdf->SetXY(170, 28.25);
        $pdf->Write(0, $fecha);

        $pdf->SetFont('Arial', '', 5);
        $pdf->SetXY(19, 40);
        $pdf->Write(0, 'Expliacion random');
        $pdf->SetXY(116, 40);
        $pdf->Write(0, $data['solicitudCompra']['motivo']);

        $pdf->SetXY(57.5, 46);
        $pdf->Write(0, $data['solicitudCompra']['id']);
        $pdf->SetXY(57.5, 48.7);
        $pdf->Write(0, 'DIRECTOR OPERATIVO');
        $pdf->SetXY(57.5, 51.5);
        $pdf->Write(0, 'CORPORACION ADMINISTRATIVA DEL SUR');
        $pdf->SetXY(57.5, 54.2);
        $pdf->Write(0, $data['solicitudCompra']['motivo']);

        $pdf->SetXY(57.5, 62);
        $pdf->Write(0, $data['proveedor']['nombre']);
        $pdf->SetXY(57.5, 64.1);
        $pdf->Write(0, $data['proveedor']['localidad']);
        $pdf->SetXY(57.5, 66.4);
        $pdf->Write(0, $data['proveedor']['contacto']);
        $pdf->SetXY(57.5, 68.6);
        $pdf->Write(0, $data['proveedor']['telefono']);
        $pdf->SetXY(57.5, 70.8);
        $pdf->Write(0, $data['proveedor']['condiciones']);

        $pdf->SetXY(170, 64);
        $pdf->Write(0, $data['cotizacion']['folio']);

        $fechaOriginalOc = $data['cotizacion']['fecha'];
        $fechaOc = date("d/m/Y", strtotime($fechaOriginalOc));

        $pdf->SetXY(170, 66.4);
        $pdf->Write(0, $fechaOc);

        $pdf->SetXY(57.5, 78.6);
        $pdf->Write(0, 'CORPORACION ADMINISTRATIVA DEL SUR SC');
        $pdf->SetXY(57.5, 80.4);
        $pdf->Write(0, 'CAS081119D49');
        $pdf->SetXY(57.5, 82.3);
        $pdf->Write(0, 'CALZADA LEGARIA 761 PISO 2');
        $pdf->SetXY(57.5, 84.3);
        $pdf->Write(0, 'COLONIA IRRIGACION');
        $pdf->SetXY(57.5, 86.3);
        $pdf->Write(0, 'MIGUEL HIDALGO');
        $pdf->SetXY(57.5, 88.3);
        $pdf->Write(0, '11500');
        $pdf->SetXY(57.5, 90.4);
        $pdf->Write(0, 'MIREYA SANTIAGO');
        $pdf->SetXY(57.5, 92.4);
        $pdf->Write(0, '(55) 26296470 Ext 6437');

        $pdf->SetXY(127, 78.6);
        $pdf->Write(0, 'CORPORACION ADMINISTRATIVA DEL SUR SC');
        $pdf->SetXY(127, 80.4);
        $pdf->Write(0, 'CAS081119D49');
        $pdf->SetXY(127, 82.3);
        $pdf->Write(0, 'CALZADA LEGARIA 761 PISO 2');
        $pdf->SetXY(127, 84.3);
        $pdf->Write(0, 'COLONIA IRRIGACION');
        $pdf->SetXY(127, 86.3);
        $pdf->Write(0, 'MIGUEL HIDALGO');
        $pdf->SetXY(127, 88.3);
        $pdf->Write(0, '11500');
        $pdf->SetXY(127, 90.4);
        $pdf->Write(0, 'ELVIRA AVILA LUA');
        $pdf->SetXY(127, 92.4);
        $pdf->Write(0, '(55) 26296470 Ext 33312');

        //Fila tabla detalle detalle
        $pdf->SetFont('Arial', 'B', 5);

        $y = 103;
        $totalImporte = 0;
        foreach ($data['detallesCotizacion'] as $detalle) {
            $cantidad = $detalle->detalle_solicitud->cantidad;
            $precio_unitario = $detalle->importe_unitario;
            $tipo = $detalle->detalle_solicitud->unidadMedida->nombre;
            $descripcion = $detalle->detalle_solicitud->descripcion;
            $observaciones = $detalle->detalle_solicitud->observaciones;
            $importe = $cantidad * $precio_unitario;

            $pdf->SetXY(19.5, $y);
            $pdf->Cell(11, 5, $cantidad, 0, '0', $align = 'C');
            $pdf->Cell(28, 5, $tipo, 0, '0', $align = 'C');
            $pdf->Cell(19.3, 5, $descripcion, 0, '0', $align = 'C');
            $pdf->Cell(23.2, 5, $descripcion, 0, '0', $align = 'C');
            $pdf->Cell(52, 5, utf8_decode($observaciones), 0, '0', $align = 'C');
            $pdf->Cell(15, 5, "$ " . number_format($precio_unitario, 2), 0, '0', $align = 'C');
            $pdf->Cell(25, 5, "$ " . number_format($importe, 2), 0, '0', $align = 'C');
            $pdf->Ln();
            $y += 5;
            $totalImporte += $importe;
        }

        $tipoCambio = 1.00;
        $pdf->SetXY(63, 220.5);
        $pdf->Write(0, 'PESOS');
        $pdf->SetXY(65, 223);
        $pdf->Write(0, $tipoCambio);


        $pdf->SetXY(138.5, 217.2);
        $pdf->Cell(14, 2,  "$ " . number_format($totalImporte, 2), 0, '0', $align = 'R');
        $pdf->SetXY(152.5, 217.2);
        $pdf->Cell(16, 2,  "$ 0.00 ", 0, '0', $align = 'R');
        $pdf->SetXY(168.5, 217.2);
        $pdf->Cell(24, 2,  "$ " . number_format($totalImporte, 2), 0, '0', $align = 'R');

        $pdf->SetXY(138.5, 219.5);
        $pdf->Cell(14, 2,  $tipoCambio, 0, '0', $align = 'R');
        $pdf->SetXY(152.5, 219.5);
        $pdf->Cell(16, 2,  $tipoCambio, 0, '0', $align = 'R');
        $pdf->SetXY(168.5, 219.5);
        $pdf->Cell(24, 2,  '$0.00', 0, '0', $align = 'R');

        $subtotal = $totalImporte * $tipoCambio;

        $pdf->SetXY(138.5, 221.9);
        $pdf->Cell(14, 2,  "$ " . number_format($subtotal, 2), 0, '0', $align = 'R');
        $pdf->SetXY(152.5, 221.9);
        $pdf->Cell(16, 2,  '$ 0.00', 0, '0', $align = 'R');
        $pdf->SetXY(168.5, 221.9);
        $pdf->Cell(24, 2,  "$ " . number_format($subtotal, 2), 0, '0', $align = 'R');

        $iva = $subtotal * 0.16;

        $pdf->SetXY(138.5, 224.4);
        $pdf->Cell(14, 2,  " ", 0, '0', $align = 'R');
        $pdf->SetXY(152.5, 224.4);
        $pdf->Cell(16, 2,  '0.00 %', 0, '0', $align = 'R');
        $pdf->SetXY(168.5, 224.4);
        $pdf->Cell(24, 2,  "", 0, '0', $align = 'R');

        $total = $subtotal + $iva;
        $pdf->SetXY(138.5, 226.9);
        $pdf->Cell(14, 2,  "$ " . number_format($iva, 2), 0, '0', $align = 'R');
        $pdf->SetXY(152.5, 226.9);
        $pdf->Cell(16, 2,  '$ 0.00', 0, '0', $align = 'R');
        $pdf->SetXY(168.5, 226.9);
        $pdf->Cell(24, 2,  "$ " . number_format($iva, 2), 0, '0', $align = 'R');


        $pdf->SetXY(138.5, 229.4);
        $pdf->Cell(14, 2,  "$ " . number_format($total, 2), 0, '0', $align = 'R');
        $pdf->SetXY(152.5, 229.4);
        $pdf->Cell(16, 2,  '$ 0.00', 0, '0', $align = 'R');
        $pdf->SetXY(168.5, 229.4);
        $pdf->Cell(24, 2,  "$ " . number_format($total, 2), 0, '0', $align = 'R');

        return $pdf->Output('S');
        // $pdf->Output();
    }

    
}
