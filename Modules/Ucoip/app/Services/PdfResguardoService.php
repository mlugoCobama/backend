<?php

namespace Modules\Ucoip\Services;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\File;

class PdfResguardoService{
        public function generarPdfResguardo($data, $nombreUsuario, $email, $empresa )
        {
            $pdf = new Fpdi();
            $pdf->AddPage('P', 'Letter');
            
            //Plantilla PDF: Formato de resguardo
            $pdf->setSourceFile(base_path('Modules/Ucoip/resources/assets/Formatos/resguardo.pdf'));
            $template = $pdf->importPage(1);
            $pdf->useImportedPage($template);

            // Fuente
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', 'B', 10);

            // $folioResguardo = $data->folio;
            // // y = -2.5
            // $pdf->SetXY(169, 27);
            // $pdf->Cell(37, 10, $folioResguardo , 0, 1 ,'C');
            // $pdf->SetFont('Arial', 'B', 9);
            // $fechaInicio = $this->fechaFormateada($data->fecha_inicio);
            $fechaInicio = $this->fechaFormateada(now());
            // $fechaFin = $this->fechaFormateada($data->fecha_fin);
            $pdf->SetXY(169, 37);
            $pdf->Cell(37, 7, $fechaInicio , 0, 1 ,'C');
            // $pdf->SetXY(169, 44);
            // $pdf->Cell(37, 7, $fechaFin , 0, 1 ,'C');
   

            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetXY(40, 61);
            $pdf->Cell(165, 7, $this->formatearTexto($empresa) , 0, 1 ,'C');

            $pdf->SetXY(40, 72);
            $pdf->Cell(165, 7, $this->formatearTexto($nombreUsuario) , 0, 1 ,'C');
            $pdf->SetXY(40, 83);
            $pdf->Cell(165, 7, $this->formatearTexto($email) , 0, 1 ,'C');

            /**
             * Detalles del resguardo
             */
            $detalles = $data;
            $pdf->SetFont('Arial', 'B', 6);
            
            $y = 132.1;

            foreach ($detalles as $detalle) {
                $pdf->SetXY(28.6, $y);
                $hardware = $detalle->hardware;
                $pdf->Cell(7, 3, '#', 0, 0, 'L');
                $pdf->Cell(21, 3, $this->formatearTexto($hardware->marca), 0, 0, 'C');
                $pdf->Cell(29, 3, $this->formatearTexto($hardware->modelo), 0, 0, 'C');
                $pdf->Cell(37, 3, $this->formatearTexto($hardware->no_serie), 0, 0, 'C');
                $pdf->Cell(16, 3, $this->formatearTexto($hardware->id), 0, 0, 'C');
                // $pdf->Cell(29, 3, $this->formatearTexto($hardware->caracteristicas), 0, 0, 'C');
                $pdf->Cell(29, 3, $this->formatearTexto($hardware->tipoHardware->tipo), 0, 0, 'C');
                $pdf->Cell(39, 3, $this->formatearTexto($hardware->observaciones), 0, 0, 'C');
                $y = $y + 3.7;
            }

            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetXY(126, 229);

            // Establecer color de fondo blanco
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(
                80,6,$this->formatearTexto($nombreUsuario),0,0,'C',true 
            );

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

}