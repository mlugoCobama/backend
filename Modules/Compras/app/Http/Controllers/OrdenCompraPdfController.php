<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\File;

class OrdenCompraPdfController extends Controller
{
    /** *********************************************************************
    * Código que genera el formato de orden de compra
    ***********************************************************************/
    public function OrdenCompraFormatoInterno($data)
    {
        //JSON de donde se obtienen los datos de facturacion
        $content = File::get(base_path('dataEntregas.json'));
        $json = json_decode(json: $content, associative: true);

        $contentE = File::get(base_path('dataEntregas.json'));
        $jsonE = json_decode(json: $contentE, associative: true);

        $dataFacturacion = $json[$data['destino'][0]->intercompania];
        $dataEntrega = $jsonE[$data['ordenCompra']['entrega']];

        $rawCentrosCosto = File::get(base_path('Modules/Compras/resources/assets/json/centrosCostos/catCentrosCostos.json'));
        $jsonCC = json_decode(json: $rawCentrosCosto, associative: true);
        $dataCC = $jsonCC[$data['solicitudCompra']['c_c']];

        $pdf = new Fpdi();
        $pdf->AddPage();
         
        //Plantilla PDF: Formato interno de compra
        // $pdf->setSourceFile(__DIR__ . "/../../../../../storage/app/modules/compras/orden_compra/formato_compras_v1.pdf");
        $pdf->setSourceFile(__DIR__ . "/../../../resources/assets/orden_compra_v3.pdf");
        $template = $pdf->importPage(1);
        $pdf->useImportedPage($template);

        // Fuente
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 7);

        $folioOC = $data['ordenCompra']['folio_oc'];
        // y = -2.5
        $pdf->SetXY(172, 12.5);
        $pdf->Write(0, $folioOC);

        $pdf->SetFont('Arial', 'B', 5);

        /*-----------------------------------------------------
         * Inicia datos de usuario solicita
        -----------------------------------------------------*/

        $areaSolicita = $data['solicita'][0]->area;
        $pdf->SetXY(23, 21.1);
        $pdf->Write(0, utf8_decode(' (' . $areaSolicita . ')'));
       
        $pdf->SetFont('Arial', 'B', 6);

        $pdf->SetXY(65, 21.1);
        // $pdf->Write(0, $data['ordenCompra']['id']);
        $usuarioSolicita = utf8_decode('' . $data['solicita'][0]->firstname . ' ' . $data['solicita'][0]->realname . '');
        $pdf->SetXY(80, 21.1);
        $pdf->Write(0, $usuarioSolicita);

        $fechaOriginal = $data['ordenCompra']['fecha'];
        $fecha = date("d/m/Y", strtotime($fechaOriginal));
        $pdf->SetXY(173.6, 21.1);
        $pdf->Write(0, $fecha);

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(18, 37);
        $pdf->Write(0, strtoupper(''));
        $pdf->SetXY(119, 37);
        $pdf->Write(0, strtoupper(''));
        // $pdf->Write(0, strtoupper(utf8_decode($data['solicitudCompra']['motivo'])));

        /*-----------------------------------------------------
         * Inicia datos de usuario destino
        -----------------------------------------------------*/
         if($data['solicitudCompra']['c_c'] === 0){
            // Datos para cualquier otra empresa
             $pdf->SetXY(57, 39.9);
             $pdf->Write(0, utf8_decode('' . $data['destino'][0]->firstname . ' ' . $data['destino'][0]->realname . ''));
             $pdf->SetXY(57, 42.6);
             $pdf->Write(0, utf8_decode($data['destino'][0]->puesto));
         }else{
            // Datos para empresas que son agencias
            $pdf->SetXY(57, 39.9);
            $pdf->Write(0, utf8_decode($dataCC['descripcion']));
            $pdf->SetXY(57, 42.6);
            $pdf->Write(0, '---------------------------------');
         }
        


        $pdf->SetXY(57, 44.8);
        $pdf->Write(0, strtoupper(utf8_decode($data['destino'][0]->empresa)));

        $pdf->SetFont('Arial', '', 5);
        $pdf->SetXY(56.9, 46.3);
        $motivo = utf8_decode($data['solicitudCompra']['motivo']);

        $textoRecortado =  substr($motivo, 0, 192) . (strlen($motivo) > 192 ? '...':'');
        // $pdf->Write(0, $textoRecortado);
        $pdf->MultiCell(100.3,2, $textoRecortado ,0,'L' );
        // $pdf->SetXY(57.5, 49);
        // $pdf->Write(0, utf8_decode($data['solicitudCompra']['motivo']));

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(57, 56);
        $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['nombre'])));
        $pdf->SetXY(57, 58.6);
        $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['localidad'])));
        $pdf->SetXY(57, 61.1);
        $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['contacto'])));
        $pdf->SetXY(57, 63.6);
        $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['telefono'])));
        $pdf->SetXY(57, 66.1);
        $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['condiciones'])));

        /*-----------------------------------------------------
         * Datos de referencia de cotización
        -----------------------------------------------------*/
        $pdf->SetXY(173.6, 58.6);
        $pdf->Write(0, utf8_decode($data['cotizacion']['folio']));
        $fechaOriginalOc = $data['cotizacion']['fecha'];
        $fechaOc = date("d/m/Y", strtotime($fechaOriginalOc));
        $pdf->SetXY(173.6, 61.4);
        $pdf->Write(0, $fechaOc);

        /*-----------------------------------------------------
         * Datos de facturación
        -----------------------------------------------------*/
        $pdf->SetXY(57, 75);
        $pdf->Write(0, $dataFacturacion['RAZON SOCIAL']);
        $pdf->SetXY(57, 77.5);
        $pdf->Write(0, $dataFacturacion['RFC']);
        $pdf->SetXY(57, 80);
        $pdf->Write(0, $dataFacturacion['DIRECCION']);
        $pdf->SetXY(57, 82.5);
        $pdf->Write(0, $dataFacturacion['COLONIA']);
        $pdf->SetXY(57, 85);
        $pdf->Write(0, $dataFacturacion['CIUDAD/DELEG/ESTADO']);
        $pdf->SetXY(57, 87.5);
        $pdf->Write(0, $dataFacturacion['C.P.']);
        $pdf->SetXY(57, 90);
        $pdf->Write(0, $dataFacturacion['CONTACTO PAGOS']);
        $pdf->SetXY(57, 92.5);
        $pdf->Write(0, $dataFacturacion['TELS.']);

        /*-----------------------------------------------------
         * Datos de entrega                                    
         -----------------------------------------------------*/
        $pdf->SetXY(130.5, 75);
        $pdf->Write(0, $dataEntrega['RAZON SOCIAL']);
        $pdf->SetXY(130.5, 77.5);
        $pdf->Write(0, $dataEntrega['RFC']);
        $pdf->SetXY(130.5, 80);
        $pdf->Write(0, $dataEntrega['DIRECCION']);
        $pdf->SetXY(130.5, 82.5);
        $pdf->Write(0, $dataEntrega['COLONIA']);
        $pdf->SetXY(130.5, 85);
        $pdf->Write(0, $dataEntrega['CIUDAD/DELEG/ESTADO']);
        $pdf->SetXY(130.5, 87.5);
        $pdf->Write(0, $dataEntrega['C.P.']);
        $pdf->SetXY(130.5, 90);
        $pdf->Write(0, $dataEntrega['CONTACTO ENTREGA']);
        $pdf->SetXY(130.5, 92.5);
        $pdf->Write(0, $dataEntrega['TELS. ENTREGA']);

        /*-----------------------------------------------------
         * Fila de la tabla  de detalles                                    
         -----------------------------------------------------*/
        $pdf->SetFont('Arial', 'B', 5.5);

        $y = 103;
        $totalImporte = 0;

        //Manejo de tabla detalles con multi lineas para textos largos
        foreach ($data['detallesCotizacion'] as $detalle) {
            $cantidad = $detalle->detalle_solicitud->cantidad;
            $precio_unitario = $detalle->importe_unitario;
            $tipo = $detalle->detalle_solicitud->unidadMedida->nombre;
            $descripcion = $detalle->detalle_solicitud->descripcion;
            $observaciones = $detalle->detalle_solicitud->observaciones;
            $eco = '';

            $detalleAutotanque = $detalle->detalle_solicitud->DetalleAutotanque;
            if(!empty($detalleAutotanque)){
               $eco = "ECO: ". $detalle->detalle_solicitud->DetalleAutotanque->DatosVehiculo->nro_economico;
            }

            $importe = $cantidad * $precio_unitario;

            // Guardar la posición actual
            $pdf->SetXY(16.2, $y);
            $pdf->Cell(12, 5, $cantidad, 0, 0, 'C');
            $pdf->Cell(16, 5, $tipo, 0, 0, 'C');

            
            $x = $pdf->GetX();
            $yBefore = $pdf->GetY();

            // Multicel: Se utiliza para campos con textos largos
            //* $pdf->MultiCell(101, 5, utf8_decode($descripcion), 0, 'C'); 
            $pdf->MultiCell(57, 5, utf8_decode($descripcion), 0, 'C'); 
            
            // Ancho y alto de línea
            $descLineHeight = $pdf->GetY() - $yBefore;

            // Posición siguiente celda
            $pdf->SetXY($x + 57, $yBefore); 
            $pdf->MultiCell(44, 5, utf8_decode($observaciones), 0, 'C');
            
            //Posición siguiente celda
            $pdf->SetXY($x + 101.5, $yBefore); 
            $pdf->MultiCell(10, 5, utf8_decode($eco), 0,'C');

            $obsLineHeight = $pdf->GetY() - $yBefore;
            
            //Alto de las siguiente celdas
            $lineHeight = max($descLineHeight, $obsLineHeight);

            $pdf->SetXY(155.5, $y);
            $pdf->Cell(17, $lineHeight, "$ " . number_format($precio_unitario, 2), 0, 0, 'R');
            $pdf->Cell(25.5, $lineHeight, "$ " . number_format($importe, 2), 0, 0, 'R');

            // actualizar para siguiente fila
            $y += $lineHeight;

            //* Dibuja la línea inferior al final de la fila
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth(0.2);
            $pdf->Line(16.7, $y, 197, $y);

            $pdf->SetY($y); 
            $totalImporte += $importe;
        }

        /*-----------------------------------------------------
         * Tabla de tipo de cambio                                    
         -----------------------------------------------------*/
        $tipoCambio = 1.00;
        $pdf->SetXY(63, 225.7);
        $pdf->Write(0, 'PESOS');
        $pdf->SetXY(65, 228.2);
        $pdf->Write(0, $tipoCambio);

        /*-----------------------------------------------------
         * Fila totales importe                                  
         -----------------------------------------------------*/
        $pdf->SetXY(141.5, 221.5);
        $pdf->Cell(14, 2.5,  "$ " . number_format($totalImporte, 2), 0, '0', $align = 'R');
        $pdf->SetXY(156, 221.5);
        $pdf->Cell(16, 2.5,  "$ 0.00 ", 0, '0', $align = 'R');
        $pdf->SetXY(172, 221.5);
        $pdf->Cell(26, 2.5,  "$ " . number_format($totalImporte, 2), 0, '0', $align = 'R');

        /*-----------------------------------------------------
         * Fila totales tipo cambio                                
         -----------------------------------------------------*/
        $pdf->SetXY(141.5, 224.5);
        $pdf->Cell(14, 2.5,  $tipoCambio, 0, '0', $align = 'R');
        $pdf->SetXY(156, 224.5);
        $pdf->Cell(16, 2.5,  $tipoCambio, 0, '0', $align = 'R');
        $pdf->SetXY(172, 224.5);
        $pdf->Cell(26, 2.5,  '$0.00', 0, '0', $align = 'R');

        $subtotal = $totalImporte * $tipoCambio;

        /*-----------------------------------------------------
         * Fila totales subtotal                               
         -----------------------------------------------------*/
        $pdf->SetXY(141.5, 226.9);
        $pdf->Cell(14, 2.5,  "$ " . number_format($subtotal, 2), 0, '0', $align = 'R');
        $pdf->SetXY(156, 226.9);
        $pdf->Cell(16, 2.5,  '$ 0.00', 0, '0', $align = 'R');
        $pdf->SetXY(172, 226.9);
        $pdf->Cell(26, 2.5,  "$ " . number_format($subtotal, 2), 0, '0', $align = 'R');

        $iva = $subtotal * 0.16;

        /*-----------------------------------------------------
         * Fila totales %IVA                               
         -----------------------------------------------------*/
        $pdf->SetXY(141.5, 229.4);
        $pdf->Cell(14, 2.5,  " ", 0, '0', $align = 'R');
        // $pdf->SetXY(156, 229.4);
        // $pdf->Cell(16, 2.5,  '0.00 %', 0, '0', $align = 'R');
        $pdf->SetXY(172, 229.4);
        $pdf->Cell(26, 2.5,  "", 0, '0', $align = 'R');

        $total = $subtotal + $iva;

        /*-----------------------------------------------------
         * Fila totales IVA                               
         -----------------------------------------------------*/
        $pdf->SetXY(141.5, 231.8);
        $pdf->Cell(14, 2.5,  "$ " . number_format($iva, 2), 0, '0', $align = 'R');
        $pdf->SetXY(156, 231.8);
        $pdf->Cell(16, 2.5,  '$ 0.00', 0, '0', $align = 'R');
        $pdf->SetXY(172, 231.8);
        $pdf->Cell(26, 2.5,  "$ " . number_format($iva, 2), 0, '0', $align = 'R');

        /*-----------------------------------------------------
         * Fila totales TOTAL                  +6              
         -----------------------------------------------------*/
        $pdf->SetXY(141.5, 234.2);
        $pdf->Cell(14, 2.5,  "$ " . number_format($total, 2), 0, '0', $align = 'R');
        $pdf->SetXY(156, 234.2);
        $pdf->Cell(16, 2.5,  '$ 0.00', 0, '0', $align = 'R');
        $pdf->SetXY(172, 234.2);
        $pdf->Cell(26, 2.5,  "$ " . number_format($total, 2), 0, '0', $align = 'R');

        return $pdf->Output('S');
        // $pdf->Output();
    }
}
