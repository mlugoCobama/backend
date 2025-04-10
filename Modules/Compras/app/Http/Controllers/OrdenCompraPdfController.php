<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\File;
use Psy\Readline\Hoa\Console;

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
    public function store(Request $request)
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
    public function update(Request $request, $id){
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
    //Código que genera el formato de orden de compra
    public function OrdenCompraFormatoInterno($data)
    {
        $content = File::get(base_path('/dataFacturacion.json'));
        $json = json_decode(json: $content, associative: true);
        $dataFacturacion = $json[$data['destino'][0]->empresa];
        $dataEntrega = $json[$data['solicita'][0]->empresa];

        $pdf = new Fpdi();
        $pdf->AddPage();

        $pdf->setSourceFile(__DIR__ . "/../../../../../storage/app/modules/compras/orden_compra/formato_compras_v1.pdf");
        

        $template = $pdf->importPage(1);
        $pdf->useImportedPage($template);


        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(172, 15.5);
        $pdf->Write(0, $data['ordenCompra']['folio_oc']);

        $pdf->SetFont('Arial', 'B', 5); 
        $pdf->SetXY(24.5, 24);
        $pdf->Write(0, utf8_decode(' ('.$data['solicita'][0]->area.')'));

        $pdf->SetFont('Arial', 'B', 6);        
        $pdf->SetXY(65, 24);
        $pdf->Write(0, $data['ordenCompra']['id']);
        $pdf->SetXY(80, 24);
        $pdf->Write(0, utf8_decode(''.$data['solicita'][0]->firstname.' '.$data['solicita'][0]->realname.''));

        $fechaOriginal = $data['ordenCompra']['fecha'];
        $fecha = date("d/m/Y", strtotime($fechaOriginal));
        $pdf->SetXY(174, 24.3);
        $pdf->Write(0, $fecha);

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(18, 37);
        $pdf->Write(0, strtoupper('Expliacion random'));
        $pdf->SetXY(119, 37);
        $pdf->Write(0, strtoupper(utf8_decode($data['solicitudCompra']['motivo'])));

        $pdf->SetXY(57.5, 42.8);
        $pdf->Write(0, utf8_decode(''.$data['destino'][0]->firstname.' '.$data['destino'][0]->realname.''));
        $pdf->SetXY(57.5, 45.4);
        $pdf->Write(0, utf8_decode($data['destino'][0]->puesto));
        $pdf->SetXY(57.5, 48);
        $pdf->Write(0, strtoupper(utf8_decode($data['destino'][0]->empresa)));
        $pdf->SetXY(57.5, 50.6);
        $pdf->Write(0,utf8_decode($data['solicitudCompra']['motivo']));

        $pdf->SetXY(57.5, 58.9);
        $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['nombre'])));
        $pdf->SetXY(57.5, 61.4);
        $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['localidad'])));
        $pdf->SetXY(57.5, 64);
        $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['contacto'])));
        $pdf->SetXY(57.5, 66.6);
        $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['telefono'])));
        $pdf->SetXY(57.5, 69.2);
        $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['condiciones'])));

        $pdf->SetXY(173, 61.4);
        $pdf->Write(0, utf8_decode($data['cotizacion']['folio']));

        $fechaOriginalOc = $data['cotizacion']['fecha'];
        $fechaOc = date("d/m/Y", strtotime($fechaOriginalOc));

        $pdf->SetXY(173, 64);
        $pdf->Write(0, $fechaOc);

        $pdf->SetXY(57.5, 77.75);
        $pdf->Write(0, $dataFacturacion['RAZON SOCIAL']);
        $pdf->SetXY(57.5, 80.3);
        $pdf->Write(0, $dataFacturacion['RFC']);
        $pdf->SetXY(57.5, 82.9);
        $pdf->Write(0, $dataFacturacion['DIRECCION']);
        $pdf->SetXY(57.5, 85.4);
        $pdf->Write(0, $dataFacturacion['COLONIA']);
        $pdf->SetXY(57.5, 87.9);
        $pdf->Write(0, $dataFacturacion['CIUDAD/DELEG/ESTADO']);
        $pdf->SetXY(57.5, 90.5);
        $pdf->Write(0, $dataFacturacion['C.P.']);
        $pdf->SetXY(57.5, 93);
        $pdf->Write(0, $dataFacturacion['CONTACTO PAGOS']);
        $pdf->SetXY(57.5, 95.5);
        $pdf->Write(0, $dataFacturacion['TELS.']);

        $pdf->SetXY(130, 77.75);
        $pdf->Write(0, $dataEntrega['RAZON SOCIAL']);
        $pdf->SetXY(130, 80.3);
        $pdf->Write(0, $dataEntrega['RFC']);
        $pdf->SetXY(130, 82.9);
        $pdf->Write(0, $dataEntrega['DIRECCION']);
        $pdf->SetXY(130, 85.4);
        $pdf->Write(0, $dataEntrega['COLONIA']);
        $pdf->SetXY(130, 87.9);
        $pdf->Write(0, $dataEntrega['CIUDAD/DELEG/ESTADO']);
        $pdf->SetXY(130, 90.5);
        $pdf->Write(0, $dataEntrega['C.P.']);
        $pdf->SetXY(130, 93);
        $pdf->Write(0, $dataEntrega['CONTACTO PAGOS']);
        $pdf->SetXY(130, 95.5);
        $pdf->Write(0, $dataEntrega['TELS.']);

        //Fila tabla detalle detalle
        $pdf->SetFont('Arial', 'B', 6);

        $y = 107;
        $totalImporte = 0;
        foreach ($data['detallesCotizacion'] as $detalle) {
            $cantidad = $detalle->detalle_solicitud->cantidad;
            $precio_unitario = $detalle->importe_unitario;
            $tipo = $detalle->detalle_solicitud->unidadMedida->nombre;
            $descripcion = $detalle->detalle_solicitud->descripcion;
            $observaciones = $detalle->detalle_solicitud->observaciones;
            $importe = $cantidad * $precio_unitario;

            $pdf->SetXY(17.5, $y);
            $pdf->Cell(12, 5, $cantidad, 0, '0', $align = 'C');
            $pdf->Cell(28, 5, $tipo, 0, '0', $align = 'C');
            $pdf->Cell(21, 5, $descripcion, 0, '0', $align = 'C');
            $pdf->Cell(24.2, 5, $descripcion, 0, '0', $align = 'C');
            $pdf->Cell(54, 5, utf8_decode($observaciones), 0, '0', $align = 'C');
            $pdf->Cell(16, 5, "$ " . number_format($precio_unitario, 2), 0, '0', $align = 'C');
            $pdf->Cell(25.5, 5, "$ " . number_format($importe, 2), 0, '0', $align = 'C');
            $pdf->Ln();
            $y += 5;
            $totalImporte += $importe;
        }

        $tipoCambio = 1.00;
        $pdf->SetXY(63, 219);
        $pdf->Write(0, 'PESOS');
        $pdf->SetXY(65, 221.5);
        $pdf->Write(0, $tipoCambio);


        $pdf->SetXY(142.5, 215.2);
        $pdf->Cell(14, 2,  "$ " . number_format($totalImporte, 2), 0, '0', $align = 'R');
        $pdf->SetXY(156.4, 215.2);
        $pdf->Cell(17, 2,  "$ 0.00 ", 0, '0', $align = 'R');
        $pdf->SetXY(173, 215.2);
        $pdf->Cell(25, 2,  "$ " . number_format($totalImporte, 2), 0, '0', $align = 'R');

        $pdf->SetXY(142.5, 218.3);
        $pdf->Cell(14, 2,  $tipoCambio, 0, '0', $align = 'R');
        $pdf->SetXY(156.4, 218.3);
        $pdf->Cell(17, 2,  $tipoCambio, 0, '0', $align = 'R');
        $pdf->SetXY(173, 218.3);
        $pdf->Cell(25, 2,  '$0.00', 0, '0', $align = 'R');

        $subtotal = $totalImporte * $tipoCambio;

        $pdf->SetXY(142.5, 220.5);
        $pdf->Cell(14, 2,  "$ " . number_format($subtotal, 2), 0, '0', $align = 'R');
        $pdf->SetXY(156.4, 220.5);
        $pdf->Cell(17, 2,  '$ 0.00', 0, '0', $align = 'R');
        $pdf->SetXY(173, 220.5);
        $pdf->Cell(25, 2,  "$ " . number_format($subtotal, 2), 0, '0', $align = 'R');

        $iva = $subtotal * 0.16;

        $pdf->SetXY(142.5, 223);
        $pdf->Cell(14, 2,  " ", 0, '0', $align = 'R');
        $pdf->SetXY(156.4, 223);
        $pdf->Cell(17, 2,  '0.00 %', 0, '0', $align = 'R');
        $pdf->SetXY(173, 223);
        $pdf->Cell(25, 2,  "", 0, '0', $align = 'R');

        $total = $subtotal + $iva;
        $pdf->SetXY(142.5, 225.5);
        $pdf->Cell(14, 2,  "$ " . number_format($iva, 2), 0, '0', $align = 'R');
        $pdf->SetXY(156.4, 225.5);
        $pdf->Cell(17, 2,  '$ 0.00', 0, '0', $align = 'R');
        $pdf->SetXY(173, 225.5);
        $pdf->Cell(25, 2,  "$ " . number_format($iva, 2), 0, '0', $align = 'R');


        $pdf->SetXY(142.5, 228);
        $pdf->Cell(14, 2,  "$ " . number_format($total, 2), 0, '0', $align = 'R');
        $pdf->SetXY(156.4, 228);
        $pdf->Cell(17, 2,  '$ 0.00', 0, '0', $align = 'R');
        $pdf->SetXY(173, 228);
        $pdf->Cell(25, 2,  "$ " . number_format($total, 2), 0, '0', $align = 'R');

        return $pdf->Output('S');
        // $pdf->Output();
    }

    
}
