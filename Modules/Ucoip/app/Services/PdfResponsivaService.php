<?php

namespace Modules\Ucoip\Services;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\File;


class PdfResponsivaService{
        public function generarPdfResponsiva($data)
        {
            $pdf = new Fpdi();
                $pdf->setSourceFile(base_path('Modules/Ucoip/resources/assets/Formatos/responsiva_de_accesos.pdf'));

                $template = $pdf->importPage(1);
                $template1 = $pdf->importPage(2);
                $template2 = $pdf->importPage(3);


                $pdf->AddPage('P', 'Letter');
                $pdf->useImportedPage($template);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('Arial', 'B', 10);


                $nombreUsuario = $data->userGlpi->firstname.' '.$data->userGlpi->realname;
                $empresa = $data->empresa->nombre;

                $fecha = $this->obtenerPartesFecha(now());


                $pdf->SetXY(120, 31.7);
                $pdf->Cell(73,4, $nombreUsuario , 0, 1 ,'L');
                $pdf->SetXY(93, 35.7);
                $pdf->Cell(80,4, $empresa , 0, 1 ,'L');


            /**
             * Sistemas
             */
                 $detalles = $data->sistemas;
                 $pdf->SetFont('Arial', '', 6);
                $pdf->SetDrawColor(210, 210, 210);
                $pdf->SetLineWidth(0.3);

                $y = 172.6;
                 foreach ($detalles as $detalle) {
                     $pdf->SetXY(13.6, $y);
                    //  $hardware = $detalle->hardware;
                    $pdf->Cell(29, 4, $detalle->sistema->nombre, 1, 0, 'C');
                    $pdf->SetXY(43.6, $y);
                    $pdf->Cell(34.5, 4, $detalle->username, 1, 0, 'C');
                    $pdf->SetXY(79.1, $y);
                    $pdf->Cell(49.3, 4, '', 1, 0, 'C');
                    $pdf->SetXY(129.4, $y);
                    $pdf->Cell(35.1, 4, '', 1, 0, 'C');
                    $pdf->SetXY(165.6, $y);
                    $pdf->Cell(39, 4, '', 1, 0, 'C');
                    // $pdf->Cell(29, 3, $this->formatearTexto($hardware->modelo), 1, 0, 'C');
                    //  $pdf->Cell(37, 3, $this->formatearTexto($hardware->no_serie), 1, 0, 'C');
                    //  $pdf->Cell(16, 3, $this->formatearTexto($hardware->no_inventario), 1, 0, 'C');
                    //      $pdf->Cell(29, 3, $this->formatearTexto($hardware->caracteristicas), 1, 0, 'C');
                    //  $pdf->Cell(29, 3, $this->formatearTexto($hardware->tipoHardware->tipo), 1, 0, 'C');
                    //  $pdf->Cell(39, 3, $this->formatearTexto($hardware->observaciones), 1, 0, 'C');
                    // $pdf->Line(14, $y + 4, 205, $y + 4);
                     $y = $y + 4;
                 }
                $pdf->SetFont('Arial', 'B', 10);

                $pdf->SetXY(147.5, 228.5);
                $pdf->Cell(80,4, 'X' , 0, 1 ,'L');

                $pdf->AddPage('P', 'Letter');
                $pdf->useImportedPage($template1);

                $pdf->SetXY(32, 180.5);
                $pdf->Cell(9,4, $fecha['dia'] , 0, 1 ,'C');
                $pdf->SetXY(58, 180.5);
                $pdf->Cell(40,4, $fecha['mes']  , 0, 1 ,'C');
                $pdf->SetXY(113, 180.5);
                $pdf->Cell(5,4, $fecha['anio']  , 0, 1 ,'C');

                $pdf->SetXY(28, 210);
                $pdf->Cell(73,4, $nombreUsuario , 0, 1 ,'L');

                $pdf->AddPage('P', 'Letter');
                $pdf->useImportedPage($template2);

                // $pdf->SetXY(28, 230);
                // $pdf->Cell(73,4, $nombreUsuario , 1, 1 ,'L');


            // Fuente


            // $folioResguardo = $data->folio;
            // // y = -2.5

            // $pdf->SetFont('Arial', 'B', 9);
            // $fechaInicio = $this->fechaFormateada($data->fecha_inicio);
            // $fechaInicio = $this->fechaFormateada(now());
            // $fechaFin = $this->fechaFormateada($data->fecha_fin);
            // $pdf->SetXY(169, 37);
            // $pdf->Cell(37, 7, $fechaInicio , 0, 1 ,'C');
            // $pdf->SetXY(169, 44);
            // $pdf->Cell(37, 7, $fechaFin , 0, 1 ,'C');


            // $pdf->SetFont('Arial', 'B', 7);
            // $pdf->SetXY(40, 61);
            // $pdf->Cell(165, 7, $this->formatearTexto($empresa) , 0, 1 ,'C');

            // $pdf->SetXY(40, 72);
            // $pdf->Cell(165, 7, $this->formatearTexto($nombreUsuario) , 0, 1 ,'C');
            // $pdf->SetXY(40, 83);
            // $pdf->Cell(165, 7, $this->formatearTexto($email) , 0, 1 ,'C');

            // /**
            //  * Detalles del resguardo
            //  */
            // $detalles = $data;
            // $pdf->SetFont('Arial', 'B', 6);

            // $y = 132.1;

            // foreach ($detalles as $detalle) {
            //     $pdf->SetXY(28.6, $y);
            //     $hardware = $detalle->hardware;
            //     $pdf->Cell(7, 3, '#', 0, 0, 'L');
            //     $pdf->Cell(21, 3, $this->formatearTexto($hardware->marca), 0, 0, 'C');
            //     $pdf->Cell(29, 3, $this->formatearTexto($hardware->modelo), 0, 0, 'C');
            //     $pdf->Cell(37, 3, $this->formatearTexto($hardware->no_serie), 0, 0, 'C');
            //     $pdf->Cell(16, 3, $this->formatearTexto($hardware->no_inventario), 0, 0, 'C');
            //     // $pdf->Cell(29, 3, $this->formatearTexto($hardware->caracteristicas), 0, 0, 'C');
            //     $pdf->Cell(29, 3, $this->formatearTexto($hardware->tipoHardware->tipo), 0, 0, 'C');
            //     $pdf->Cell(39, 3, $this->formatearTexto($hardware->observaciones), 0, 0, 'C');
            //     $y = $y + 3.7;
            // }

            // $pdf->SetFont('Arial', 'B', 7);
            // $pdf->SetXY(126, 229);

            // // Establecer color de fondo blanco
            // $pdf->SetFillColor(255, 255, 255);
            // $pdf->Cell(
            //     80,6,$this->formatearTexto($nombreUsuario),0,0,'C',true
            // );

            return $pdf->Output('S');
            // $pdf->Output();
        }

        public function fechaFormateada($fechaOriginal){
            $fechaFormateada = '  ----------  ';
            if(!empty($fechaOriginal)){
                $fechaFormateada = date("d/m/Y", strtotime($fechaOriginal));
            }

            return $fechaFormateada;

        }

        function formatearTexto($cadena)
        {
            $cadena = mb_convert_encoding($cadena, 'UTF-8', mb_detect_encoding($cadena, ['UTF-8', 'ISO-8859-1', 'ASCII']));
            $cadena = trim($cadena);
            $cadena = preg_replace('/\s+/', ' ', $cadena);
            $cadena = utf8_decode($cadena);
            return $cadena;
        }

        function obtenerPartesFecha($fecha): array
        {
            if (empty($fecha)) {
                return [
                    'dia' => null,
                    'mes' => null,
                    'anio' => null,
                ];
            }

            // Parse de la fecha y configuración en español
            $carbonFecha = Carbon::parse($fecha)->locale('es');

            return [
                'dia'  => $carbonFecha->format('d'),            // "11" o "05" (usa 'j' para sin cero inicial "5")
                'mes'  => ucfirst($carbonFecha->translatedFormat('F')), // "Septiembre" (con la primera letra en mayúscula)
                'anio' => $carbonFecha->format('y'),            // "2026"
            ];
        }

}
