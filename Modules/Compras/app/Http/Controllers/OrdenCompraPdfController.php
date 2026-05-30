<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\File;
use SebastianBergmann\Diff\Line;

class OrdenCompraPdfController extends Controller
{
    /** *********************************************************************
    * Código que genera el formato de orden de compra
    ***********************************************************************/
        public function OrdenCompraFormatoInterno1($data)
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
            $pdf->AddPage('P', 'Letter');
            
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
                $pdf->Cell(12, 4.5, $cantidad, 0, 0, 'C');
                $pdf->Cell(16, 4.5, utf8_decode($tipo), 0, 0, 'C');

                
                $x = $pdf->GetX();
                $yBefore = $pdf->GetY();

                // Multicel: Se utiliza para campos con textos largos
                //* $pdf->MultiCell(101, 5, utf8_decode($descripcion), 0, 'C'); 
                $pdf->MultiCell(57, 4.5, utf8_decode($descripcion), 0, 'C'); 
                
                // Ancho y alto de línea
                $descLineHeight = $pdf->GetY() - $yBefore;

                // Posición siguiente celda
                $pdf->SetXY($x + 57, $yBefore); 
                $pdf->MultiCell(44, 4.5, utf8_decode($observaciones), 0, 'C');
                
                //Posición siguiente celda
                $pdf->SetXY($x + 101.5, $yBefore); 
                $pdf->MultiCell(10, 4.5, utf8_decode($eco), 0,'C');

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

        public function OrdenCompraFormatoInterno($data)
        {
            //JSON de donde se obtienen los datos de facturacion
            $content = File::get(base_path('dataEntregas.json'));
            $json = json_decode(json: $content, associative: true);

            $contentE = File::get(base_path('dataEntregas.json'));
            $jsonE = json_decode(json: $contentE, associative: true);

            $dataFacturacion = $json[$data['solicitudCompra']['empresa']];
            $dataEntrega = $jsonE[$data['ordenCompra']['entrega']];

            $rawCentrosCosto = File::get(base_path('Modules/Compras/resources/assets/json/centrosCostos/catCentrosCostos.json'));
            $jsonCC = json_decode(json: $rawCentrosCosto, associative: true);
            $dataCC = $jsonCC[$data['solicitudCompra']['c_c']];

            // Límites de la tabla
            $yInicioTabla = 103;
            $limiteInferior = 210;
            
            // Calcular alturas de todas las filas y número de páginas necesarias
            $alturasFilas = [];
            $totalPaginas = 1;
            $ySimulado = $yInicioTabla;
            
            foreach ($data['detallesCotizacion'] as $detalle) {
                $descripcion = $detalle->detalle_solicitud->descripcion;
                $observaciones = $detalle->detalle_solicitud->observaciones;
                
                // Calcular altura aproximada basada en longitud de texto
                $lineasDescripcion = max(1, ceil(strlen($descripcion) / 40)); // ~45 caracteres por línea
                $lineasObservaciones = max(1, ceil(strlen($observaciones) / 42)); // ~35 caracteres por línea
                
                $alturaFila = (max($lineasDescripcion, $lineasObservaciones)) * 4.4;
                // $alturaFila = $lineasDescripcion * 4.5;

                $alturasFilas[] = $alturaFila;
                
                // Verificar si necesitamos nueva página
                if ($ySimulado + $alturaFila > $limiteInferior) {
                    $totalPaginas++;
                    $ySimulado = $yInicioTabla;
                }
                
                $ySimulado += $alturaFila;
            }
            
            // Generar el PDF con las páginas calculadas
            $pdf = new Fpdi();
            
            $paginaActual = 1;
            $totalImporte = 0;
            $y = $yInicioTabla;
            $detalleIndex = 0;

            foreach ($data['detallesCotizacion'] as $index => $detalle) {
                $alturaFila = $alturasFilas[$index];

                // Si la fila no cabe en la página actual, crear nueva página
                if ($y + $alturaFila > $limiteInferior && $detalleIndex > 0) {
                    $this->agregarTotalesIntermedio($pdf);

                    $paginaActual++;
                    $pdf->AddPage('P', 'Letter');

                    $pdf->setSourceFile(__DIR__ . "/../../../resources/assets/orden_compra_v4.pdf");
                    $template = $pdf->importPage(1);
                    $pdf->useImportedPage($template);

                    $this->agregarEncabezado($pdf, $data, $dataFacturacion, $dataEntrega, $dataCC, $paginaActual, $totalPaginas);

                    $y = $yInicioTabla;
                }

                // Primera fila → crear primera página
                if ($detalleIndex == 0) {
                    $pdf->AddPage('P', 'Letter');
                    $pdf->setSourceFile(__DIR__ . "/../../../resources/assets/orden_compra_v4.pdf");
                    $template = $pdf->importPage(1);
                    $pdf->useImportedPage($template);

                    $this->agregarEncabezado($pdf, $data, $dataFacturacion, $dataEntrega, $dataCC, $paginaActual, $totalPaginas);
                }

                // Datos
                $cantidad = $detalle->detalle_solicitud->cantidad;
                $precio_unitario = $detalle->importe_unitario;
                $tipo = $detalle->detalle_solicitud->unidadMedida->nombre;
                $descripcion =  str_replace("•", "-", $detalle->detalle_solicitud->descripcion);
                $observaciones =  str_replace("•", "-", $detalle->detalle_solicitud->observaciones);
                $eco = '';

                $detalleAutotanque = $detalle->detalle_solicitud->DetalleAutotanque;
                if (!empty($detalleAutotanque)) {
                    $eco = "ECO: " . $detalle->detalle_solicitud->DetalleAutotanque->DatosVehiculo->nro_economico;
                }

                $importe = $cantidad * $precio_unitario;

                // Posición inicial de la fila
                $x = 16.2;
                $yBefore = $y;

                $pdf->SetFont('Arial', 'B', 5.5);

                // Cantidad
                $pdf->SetXY($x, $yBefore);
                $pdf->Cell(12, 4.5, $cantidad, 0, 0, 'C');

                // Tipo
                $pdf->Cell(16, 4.5, utf8_decode($tipo), 0, 0, 'C');

                // Descripción
                $pdf->SetXY($x + 28, $yBefore);
                $pdf->MultiCell(57, 4.5, utf8_decode($descripcion), 0, 'C');
                $descLineHeight = $pdf->GetY() - $yBefore;

                // Observaciones
                $pdf->SetXY($x + 85, $yBefore);
                $pdf->MultiCell(44, 4.5, utf8_decode($observaciones), 0, 'C');
                $obsLineHeight = $pdf->GetY() - $yBefore;

                // ECO
                $pdf->SetXY($x + 129, $yBefore);
                $pdf->MultiCell(10, 4.5, utf8_decode($eco), 0, 'C');
                $ecoLineHeight = $pdf->GetY() - $yBefore;

                // Altura máxima de la fila
                $lineHeight = max($descLineHeight, $obsLineHeight, $ecoLineHeight);

                // Precio unitario
                $pdf->SetXY($x + 139, $yBefore);
                $pdf->Cell(17, $lineHeight, "$ " . number_format($precio_unitario, 2), 0, 0, 'R');

                // Importe
                $pdf->Cell(25.5, $lineHeight, "$ " . number_format($importe, 2), 0, 0, 'R');

                // Actualizar Y
                $y = $yBefore + $lineHeight;
                $pdf->SetY($y);

                // Línea de separación
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->SetLineWidth(0.2);
                $pdf->Line(16.7, $y, 197, $y);

                $totalImporte += $importe;
                $detalleIndex++;
            }

            // Agregar totales finales en la última página
            $this->agregarTotalesFinales($pdf, $totalImporte);

            return $pdf->Output('S');
        }

        private function agregarEncabezado($pdf, $data, $dataFacturacion, $dataEntrega, $dataCC, $paginaActual, $totalPaginas)
        {
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', 'B', 7);

            // Folio OC
            $folioOC = $data['ordenCompra']['folio_oc'];
            $pdf->SetXY(172, 12.5);
            $pdf->Write(0, $folioOC);

            $pdf->SetFont('Arial', 'B', 5);

            // Datos de usuario solicita
            $areaSolicita = $data['solicita'][0]->area;
            $pdf->SetXY(23, 21.1);
            $pdf->Write(0, utf8_decode(' (' . $areaSolicita . ')'));
        
            $pdf->SetFont('Arial', 'B', 6);
            $usuarioSolicita = utf8_decode('' . $data['solicita'][0]->firstname . ' ' . $data['solicita'][0]->realname . '');
            $pdf->SetXY(80, 21.1);
            $pdf->Write(0, $usuarioSolicita);

            $fechaOriginal = $data['ordenCompra']['fecha'];
            $fecha = date("d/m/Y", strtotime($fechaOriginal));
            $pdf->SetXY(173.6, 21.1);
            $pdf->Write(0, $fecha);

            $pdf->SetFont('Arial', '', 6);

            // Datos de usuario destino
            if($data['solicitudCompra']['c_c'] === 0){
                $pdf->SetXY(56.5, 37.9);
                $pdf->Write(0, utf8_decode('' . $data['destino'][0]->firstname . ' ' . $data['destino'][0]->realname . ''));
                $pdf->SetXY(56.5, 40.6);
                $pdf->Write(0, utf8_decode($data['destino'][0]->puesto));
            }else{
                $pdf->SetXY(56.5, 37.9);
                $pdf->Write(0, utf8_decode($dataCC['descripcion']));
                $pdf->SetXY(56.5, 40.6);
                $pdf->Write(0, '---------------------------------');
            }

            $pdf->SetXY(56.5, 43);
            $pdf->Write(0, strtoupper(utf8_decode($this->setEmpresaName($data['solicitudCompra']['empresa']))));

            $pdf->SetFont('Arial', '', 5);
            $pdf->SetXY(56.5, 44.3);
            $motivo = $this->formatearCadena($data['solicitudCompra']['motivo']);
            $textoRecortado =  substr($motivo, 0, 230) . (strlen($motivo) > 230 ? '...':'');
            $pdf->MultiCell(100, 2, $textoRecortado, 0, 'L');

            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(56.5, 56);
            $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['nombre'])));
            $pdf->SetXY(56.5, 58.6);
            $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['localidad'])));
            $pdf->SetXY(56.5, 61.1);
            $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['contacto'])));
            $pdf->SetXY(56.5, 63.6);
            $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['telefono'])));
            // $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['datosPago'])));
            $pdf->SetXY(56.5, 66.1);
            // $pdf->Write(0, strtoupper(utf8_decode($data['proveedor']['condiciones'])));
            
            $fechaE = 'Dato No Disponible';
            $modoPago = $data['proveedor']['condiciones'];

            if(!empty($data['ordenCompra']['modo_pago'])){
                $modoPago = $data['ordenCompra']['modo_pago'] == 1 ? 'CONTADO' : 'CREDITO';
            }

            if(!empty($data['ordenCompra']['fecha_entrega'])){
                $fechaOriginalE = $data['ordenCompra']['fecha_entrega'] ;
                $fechaE = date("d/m/Y", strtotime($fechaOriginalE));
            }

            $pdf->Write(0, strtoupper(utf8_decode($modoPago)));
            $pdf->SetXY(150, 66.1);
            $pdf->Write(0, strtoupper(utf8_decode('Fecha de entrega: '. $fechaE)));
            // Datos de referencia de cotización
            $pdf->SetXY(173.6, 58.6);
            $pdf->Write(0, utf8_decode($data['cotizacion']['folio']));
            $fechaOriginalOc = $data['cotizacion']['fecha'];
            $fechaOc = date("d/m/Y", strtotime($fechaOriginalOc));
            $pdf->SetXY(173.6, 61.4);
            $pdf->Write(0, $fechaOc);

            // Datos de facturación
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

            // Datos de entrega
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
            
            // Paginación al final de la página
            $pdf->SetFont('Arial', '', 7);
            $pdf->SetXY(170, 255);
            $pdf->Cell(0, 0, utf8_decode("Página " . $paginaActual . " de " . $totalPaginas), 0, 0, 'C');


            // if(isset($data['proveedor']['datosPago']) && !empty($data['proveedor']['datosPago'])){
            //     $pdf->SetXY(60, 242);
            //     $pdf->Write(0, 'Datos de pago: ');
            //     $datosPago = $data['proveedor']['datosPago'];
            //     $incio = 245;
                
            //     for ($i=0; $i < count($datosPago) ; $i++) { 
            //        $texto = 'Banco: '.$datosPago[$i]['banco'].
            //                 ' No. Cuenta: '.$datosPago[$i]['no_cuenta'].
            //                 ' CLABE: '.$datosPago[$i]['clave_interbancaria'].
            //                 ' Beneficiario: '.$datosPago[$i]['beneficiario'];
            //        $pdf->SetXY(60, $incio);
            //        $pdf->Write(0, $texto);
            //        $incio +=4;
            //     }
            // }
        }

        private function agregarTotalesIntermedio($pdf)
        {
            $pdf->SetFont('Arial', 'B', 5.5);
            $tipoCambio = 1.00;

            // Tipo de cambio
            $pdf->SetXY(63, 225.7);
            $pdf->Write(0, 'PESOS');
            $pdf->SetXY(65, 228.2);
            $pdf->Write(0, $tipoCambio);

            // Totales en guiones
            $pdf->SetXY(141.5, 221.5);
            $pdf->Cell(14, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');
            $pdf->SetXY(156, 221.5);
            $pdf->Cell(16, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');
            $pdf->SetXY(172, 221.5);
            $pdf->Cell(26, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');

            $pdf->SetXY(141.5, 224.5);
            $pdf->Cell(14, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');
            $pdf->SetXY(156, 224.5);
            $pdf->Cell(16, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');
            $pdf->SetXY(172, 224.5);
            $pdf->Cell(26, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');

            $pdf->SetXY(141.5, 226.9);
            $pdf->Cell(14, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');
            $pdf->SetXY(156, 226.9);
            $pdf->Cell(16, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');
            $pdf->SetXY(172, 226.9);
            $pdf->Cell(26, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');

            $pdf->SetXY(141.5, 229.4);
            $pdf->Cell(14, 2.5, " ", 0, '0', $align = 'R');
            $pdf->SetXY(172, 229.4);
            $pdf->Cell(26, 2.5, " ", 0, '0', $align = 'R');

            $pdf->SetXY(141.5, 231.8);
            $pdf->Cell(14, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');
            $pdf->SetXY(156, 231.8);
            $pdf->Cell(16, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');
            $pdf->SetXY(172, 231.8);
            $pdf->Cell(26, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');

            $pdf->SetXY(141.5, 234.2);
            $pdf->Cell(14, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');
            $pdf->SetXY(156, 234.2);
            $pdf->Cell(16, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');
            $pdf->SetXY(172, 234.2);
            $pdf->Cell(26, 2.5, " - - - - - - - - - - - ", 0, '0', $align = 'R');
        }

        private function agregarTotalesFinales($pdf, $totalImporte)
        {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(16.5, 252);
            $pdf->Write(0, 'CP. MA. ZITLALI BELTRAN ALMAZAN');
            $pdf->SetFont('Arial', '', 5);
            $pdf->SetXY(23, 255);
            $pdf->Write(0, 'DIRECTORA ADMINISTRATIVA');

            $pdf->SetFont('Arial', 'B', 5.5);
            $tipoCambio = 1.00;

            // Tipo de cambio
            $pdf->SetXY(63, 225.7);
            $pdf->Write(0, 'PESOS');
            $pdf->SetXY(65, 228.2);
            $pdf->Write(0, $tipoCambio);

            // Importe
            $pdf->SetXY(141.5, 221.8);
            $pdf->Cell(14, 2.5, "$ " . number_format($totalImporte, 2), 0, '0', $align = 'R');
            $pdf->SetXY(156, 221.8);
            $pdf->Cell(16, 2.5, "$ 0.00", 0, '0', $align = 'R');
            $pdf->SetXY(172, 221.8);
            $pdf->Cell(26, 2.5, "$ " . number_format($totalImporte, 2), 0, '0', $align = 'R');

            // Tipo cambio
            $pdf->SetXY(141.5, 224.5);
            $pdf->Cell(14, 2.5, $tipoCambio, 0, '0', $align = 'R');
            $pdf->SetXY(156, 224.5);
            $pdf->Cell(16, 2.5, $tipoCambio, 0, '0', $align = 'R');
            $pdf->SetXY(172, 224.5);
            $pdf->Cell(26, 2.5, '$0.00', 0, '0', $align = 'R');

            $subtotal = $totalImporte * $tipoCambio;

            // Subtotal
            $pdf->SetXY(141.5, 226.9);
            $pdf->Cell(14, 2.5, "$ " . number_format($subtotal, 2), 0, '0', $align = 'R');
            $pdf->SetXY(156, 226.9);
            $pdf->Cell(16, 2.5, '$ 0.00', 0, '0', $align = 'R');
            $pdf->SetXY(172, 226.9);
            $pdf->Cell(26, 2.5, "$ " . number_format($subtotal, 2), 0, '0', $align = 'R');

            $iva = $subtotal * 0.16;

            // % IVA
            $pdf->SetXY(141.5, 229.4);
            $pdf->Cell(14, 2.5, " ", 0, '0', $align = 'R');
            $pdf->SetXY(172, 229.4);
            $pdf->Cell(26, 2.5, "", 0, '0', $align = 'R');

            // IVA
            $pdf->SetXY(141.5, 232);
            $pdf->Cell(14, 2.5, "$ " . number_format($iva, 2), 0, '0', $align = 'R');
            $pdf->SetXY(156, 232);
            $pdf->Cell(16, 2.5, '$ 0.00', 0, '0', $align = 'R');
            $pdf->SetXY(172, 232);
            $pdf->Cell(26, 2.5, "$ " . number_format($iva, 2), 0, '0', $align = 'R');

            $total = $subtotal + $iva;

            // TOTAL
            $pdf->SetXY(141.5, 234.5);
            $pdf->Cell(14, 2.5, "$ " . number_format($total, 2), 0, '0', $align = 'R');
            $pdf->SetXY(156, 234.5);
            $pdf->Cell(16, 2.5, '$ 0.00', 0, '0', $align = 'R');
            $pdf->SetXY(172, 234.5);
            $pdf->Cell(26, 2.5, "$ " . number_format($total, 2), 0, '0', $align = 'R');
        }

        private function setEmpresaName($intercompania)
        {
            $empresasCache = [];
            $empresas = DB::connection('intranet')->select('CALL SP_GetEmpresas()');
            
            foreach ($empresas as $empresa) {
                $empresasCache[$empresa->intercompania] = $empresa->name;
            }

            return $empresasCache[$intercompania] ?? 'Empresa fuera del catalogo';
        }

        function formatearCadena($texto) {

            $texto = str_replace(["\r\n", "\n", "\r", "•"], ' ', $texto);
            $texto = trim(preg_replace('/\s+/', ' ', $texto));
            $texto = mb_strtolower($texto);
            $texto = ucfirst($texto);
            $texto = preg_replace_callback('/(\.\s*)([a-záéíóúñ])/', function ($matches) {
                return $matches[1] . mb_strtoupper($matches[2]);
            }, $texto);
            $texto = utf8_decode($texto);
            return $texto;
        }




}
