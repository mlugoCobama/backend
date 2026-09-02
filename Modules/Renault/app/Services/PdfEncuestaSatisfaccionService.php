<?php

namespace Modules\Renault\Services;

use Modules\Renault\Models\RenCitasServicio;
use Modules\Renault\Services\CustomFpdi;
class PdfEncuestaSatisfaccionService
{

    public function getEncuestaCita($idCita){
        $cita =  RenCitasServicio::with('encuesta.repuestas')->find($idCita);
        return $cita;
    }

    public function generarPdf($idCita){
        $cita = $this->getEncuestaCita($idCita);
        $encuesta = $cita->encuesta;
        $respuestas = $encuesta->repuestas;

        // $pdf = new Fpdi();
        $pdf = new CustomFpdi();
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->AddPage('P', 'Letter');
        $path = base_path('Modules/Renault/resources/assets/encuesta_satisfaccion.pdf');

        $pdf->setSourceFile($path);
        $template = $pdf->importPage(1);
        $pdf->useImportedPage($template);

        $firma = storage_path( 'app/renault/citas_servicio/'.$cita->folio.'_firma.png');

        $this->texto($pdf, date("d/m/Y", strtotime($encuesta->fecha))  ?? '', 157,26,35,6,11);

        $cliente =  $cita->nombre.' '.$cita->apellido_paterno.' '.$cita->apellido_materno;

        $this->texto($pdf,$cliente ?? '', 35,33.2,72,6,11);

        $this->texto($pdf,$cita->email ?? '', 124,33.2,60,6,11);

        $this->texto($pdf,$cita->telefono ?? '', 39,40.5,60,6,11);


        $vehiculo = $cita->modelo.' '.$cita->anio.' '.$cita->color;
         $this->texto(
            $pdf,$vehiculo ?? '', 108,48.5,43,6,11
        );

        $this->texto(
            $pdf,$cita->vin ?? '', 162,48.5,45,6,11
        );

        $this->texto($pdf, $cliente ?? '', 61,72,130,6,11);

        $texto  =  $this->setNameAgencia($cita->agencia_id).' AGRADECE SU PREFERENCIA';
        $this->texto($pdf, $texto, 101,80.3,100,6,11, 'B',  0);
        $this->texto($pdf, $this->setNameAgencia($cita->agencia_id), 105,97,100,6,12, 'B',  0);
        $yInicial = 107;

        $distanciaEntreFilas = 32.3;

        foreach ($respuestas as $index => $respuesta) {

            $yActual = $yInicial + ($index * $distanciaEntreFilas);
            $puntuacion = $respuesta->puntuacion;
            $this->marcarEstrella($pdf, $puntuacion, $yActual);
            $motivo = $respuesta->motivo ?? '';

            $this->texto($pdf, $motivo, 106, $yActual + 5, 100,6,11);

        }

        $pdf->Image($firma, 90, 248, 60);
        $this->texto(
            $pdf,$cliente ?? '', 75, 260,65,6,8,'B',0,'C'
        );
        return $pdf->Output('S');
    }


    private function texto(
        $pdf,
        string $texto,
        float $x,
        float $y,
        float $ancho,
        float $altoLinea = 5,
        float $fontSize = 10,
        string $estilo = 'B',
        int $borde = 0,
        string $aling = 'L'
    ): void {
        $pdf->SetFont('Arial', $estilo, $fontSize);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);

        $pdf->SetXY($x, $y);
        $textoLimpio =  $this->limpiarTexto($texto);

        $pdf->MultiCell(
            $ancho,
            $altoLinea,
            $textoLimpio,
            $borde,
            $aling,
            true
        );
    }


    /**
     * Marca la estrella correspondiente a la puntuación.
     *
     */
    private function marcarEstrella(
     $pdf,
    int $puntuacion,
    float $yPosition
        ): void {
            if ($puntuacion < 1 || $puntuacion > 5) {
                return;
            }

            $estrellas = [
                1 => ['x' => 26,  'y' => $yPosition],
                2 => ['x' => 38,  'y' => $yPosition],
                3 => ['x' => 50, 'y' => $yPosition],
                4 => ['x' => 62, 'y' => $yPosition],
                5 => ['x' => 74, 'y' => $yPosition],
            ];

            $pos = $estrellas[$puntuacion];

            $pdf->SetDrawColor(10, 4, 64);
            $pdf->SetLineWidth(0.8);

            // Usamos el centro exacto de la estrella y un radio de 9mm
            $pdf->Ellipse($pos['x'], $pos['y'], 6, 6);
        }


    /**
     * Limpia caracteres que pueden causar problemas
     * con las fuentes estándar de FPDF.
     */
    private function limpiarTexto(string $texto): string
    {
        return iconv(
            'UTF-8',
            'windows-1252//TRANSLIT',
            $texto
        ) ?: $texto;
    }

    private function setNameAgencia($idAgencia){
        return match ($idAgencia){
            1 => 'AZCAPOTZALCO',
            2 => 'ECATEPEC',
            3 => 'VALLEJO',
            4 => 'PACHUCA',
             default => $idAgencia,
        };
    }
}
