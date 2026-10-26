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
                $pdf->setSourceFile(base_path('Modules/Ucoip/resources/assets/Formatos/responsiva_acceso_sitemas_v2_2.pdf'));

                $template = $pdf->importPage(1);
                $template1 = $pdf->importPage(2);
                $template2 = $pdf->importPage(3);


                $pdf->AddPage('P', 'Letter');
                $pdf->useImportedPage($template);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('Arial', 'B', 9);


                $nombreUsuario = $this->formatearTexto($data->userGlpi->firstname.' '.$data->userGlpi->realname) ;
                $empresa = $this->formatearTexto($data->empresa->nombre);
                $area =$this->formatearTexto($data->userGlpi->area ?? '') ;
                $puesto =$this->formatearTexto($data->userGlpi->puesto ?? '') ;

                $fecha = $this->obtenerPartesFecha(now());


                $pdf->SetXY(121, 31.7);
                $pdf->Cell(73,4, $nombreUsuario , 0, 1 ,'L');
                $pdf->SetXY(93, 35.7);
                $pdf->Cell(80,4, $empresa , 0, 1 ,'L');

                $pdf->SetXY(72, 63.8);
                $pdf->Cell(80,4, $puesto , 0, 1 ,'L');

                $pdf->SetXY(99, 67.8);
                $pdf->Cell(80,4, $area , 0, 1 ,'L');

            /**
             * Sistemas
             */
                $detalles = $data->sistemas;
                $pdf->SetFont('Arial', 'B', 8);
                // $pdf->SetDrawColor(210, 210, 210);
                $pdf->SetLineWidth(0.2);
                $y = 161;
                $lineHeight = 4; // Altura de cada línea de texto dentro de la celda

                foreach ($detalles as $detalle) {
                    // Definimos el contenido de cada columna
                    $col1 = $detalle->sistema->nombre ?? '';
                    $col2 = $detalle->username ?? '';
                    $col3 = $detalle->rol ?? '';
                    $col4 = $detalle->area_aplicacion ?? '';

                    // Anchos de cada columna (suman 186.9 mm)
                    $w1 = 35;
                    $w2 = 55;
                    $w3 = 56;
                    $w4 = 45;

                    // Calculo de lineas por celda
                    $nb1 = $pdf->GetStringWidth($col1) > 0 ? (($pdf->GetStringWidth($col1)+2)/ $w1) : 1;
                    $nb2 = $pdf->GetStringWidth($col2) > 0 ? (($pdf->GetStringWidth($col2)+2) / $w2) : 1;
                    $nb3 = $pdf->GetStringWidth($col3) > 0 ? ($pdf->GetStringWidth($col3) / $w3) : 1;
                    $nb4 = $pdf->GetStringWidth($col4) > 0 ? ($pdf->GetStringWidth($col4) / $w4) : 1;

                    $maxLines = max(ceil($nb1), ceil($nb2), ceil($nb3), ceil($nb4), 1);
                    $rowHeight = $maxLines * $lineHeight;

                    // Columna 1: Sistema
                    $pdf->SetXY(13.5, $y);
                    $pdf->MultiCell($w1, $lineHeight, $col1, 0, 'L');

                    // Columna 2: Username
                    $pdf->SetXY(48, $y);
                    $pdf->MultiCell($w2, $lineHeight, $col2, 0, 'L');

                    // Columna 3: Rol
                    $pdf->SetXY(106, $y);
                    $pdf->MultiCell($w3, $lineHeight, $col3, 0, 'L');

                    // Columna 4: Área / Aplicación
                    $pdf->SetXY(158, $y);
                    $pdf->MultiCell($w4, $lineHeight, $col4, 0, 'L');

                    $y += $rowHeight;
                    $pdf->Line(13, $y, 203, $y);
                }

                $pdf->Line(12.8, 160.5, 12.8, $y);
                $pdf->Line(47.75, 160.5, 47.75, $y);
                $pdf->Line(105.2, 160.5, 105.2, $y);
                $pdf->Line(158.2, 160.5, 158.2, $y);
                $pdf->Line(203.15, 160.5, 203.15, $y);

                $pdf->SetFont('Arial', 'B', 9);

                // $pdf->SetXY(148.8, 224);
                $pdf->SetXY(148.8, 203);
                $pdf->Cell(80,4, 'X' , 0, 1 ,'L');

                $pdf->AddPage('P', 'Letter');
                $pdf->useImportedPage($template1);

                 $pdf->SetXY(32, 180.5);
                 $pdf->Cell(9,4, $fecha['dia'] , 0, 1 ,'C');
                 $pdf->SetXY(58, 180.5);
                 $pdf->Cell(40,4, $fecha['mes']  , 0, 1 ,'C');
                 $pdf->SetXY(113, 180.5);
                 $pdf->Cell(5,4, $fecha['anio']  , 0, 1 ,'C');

                $pdf->SetXY(27, (211.5));
                $pdf->Cell(73,4, $nombreUsuario , 0, 1 ,'L');
                $pdf->SetXY(26, (220));
                $pdf->Cell(80,4, $puesto , 0, 1 ,'L');


                // $pdf->SetXY(127, (211.5-20));
                // $pdf->Cell(73,4, $this->formatearTexto('Dulce Consuelo Rojas Monroy')  , 0, 1 ,'L');
                // $pdf->SetXY(126, (220-20));
                // $pdf->Cell(80,4, $this->formatearTexto('Director de Marca Renault') , 0, 1 ,'L');


                // $pdf->SetXY(83, (231.5));
                // $pdf->Cell(73,4,$this->formatearTexto('Ángel Enrique Moreno Pineda') , 0, 1 ,'L');
                // $pdf->SetXY(95, (239));
                // $pdf->Cell(80,4, $this->formatearTexto('Auxiliar de Sistemas') , 0, 1 ,'L');

                $pdf->AddPage('P', 'Letter');
                $pdf->useImportedPage($template2);

                // $pdf->SetXY(28, 230);
                // $pdf->Cell(73,4, $nombreUsuario , 1, 1 ,'L');

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

        public function procesarCsvYGuardarPdfs($pathCsv, $agencia, $pathDestino = null)
    {
        if (!File::exists($pathCsv)) {
            throw new \Exception("El archivo CSV no existe en la ruta: {$pathCsv}");
        }

        // $agencia = 'Azcapotzalco';

        // Definir carpeta de salida por defecto en storage/app/responsivas
        if (!$pathDestino) {
            $pathDestino = storage_path('app/responsivas/'.$agencia);
        }

        if (!File::isDirectory($pathDestino)) {
            File::makeDirectory($pathDestino, 0755, true);
        }

        $generados = [];
        $handle = fopen($pathCsv, 'r');

        // Leer cabecera para mapear por nombres de columna o usar índices
        $headers = fgetcsv($handle, 1000, ',');

        // Limpiar BOM o espacios en la cabecera si los hubiera
        if ($headers && isset($headers[0])) {
            $headers[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $headers[0]);
        }

        while (($fila = fgetcsv($handle, 1000, ',')) !== false) {
            if (empty(array_filter($fila))) {
                continue; // Saltar filas vacías
            }

            // Mapeo dinámico fila -> campos del CSV según la imagen
            $row = array_combine($headers, $fila);

            // Construir el objeto estandarizado de datos esperado por generarPdfResponsiva
            $data = $this->mapearFilaCsvAObjeto($row, $agencia );

            // Generar el contenido del PDF en binario
            $pdfContent = $this->generarPdfResponsiva($data);

            // Nombre único para el archivo basado en el Titular
            $nombreLimpio = $row['Ucoip'];
            $nombreArchivo = "responsiva_$nombreLimpio".'_'.time() . ".pdf";
            $rutaCompleta = $pathDestino . DIRECTORY_SEPARATOR . $nombreArchivo;

            // Guardar el PDF en el servidor
            File::put($rutaCompleta, $pdfContent);

            $generados[] = [
                'titular' => $row['Titular'] ?? 'N/A',
                'archivo' => $nombreArchivo,
                'ruta' => $rutaCompleta
            ];
        }

        fclose($handle);

        return $generados;
    }

    /**
     * Mapea un arreglo asociativo con las columnas del CSV
     */
    private function mapearFilaCsvAObjeto(array $row, $agencia)
    {
        $data = new \stdClass();
        $data->userGlpi = (object) [
            'firstname' => $row['Titular'] ?? '',
            'realname'  => '',
            'puesto' => $row['Puesto'] ?? '',
            'area' => $row['Área'] ?? '',
        ];

        $data->empresa = (object) [
            'nombre' => $agencia
        ];


        $sistemasCsv = [
            // 'Ucoip'            => $row['Ucoip'] ?? null,
            'Acceso al equipo' => $row['Acceso al equipo'] ?? null,
            'Autosystem'       => $row['Autosystem'] ?? null,
            'Winsys'           => $row['Winsys'] ?? null,
            'Intranet'         => $row['Intranet'] ?? null,
            'Correo'           => $row['Correo'] ?? null,
        ];

        $data->sistemas = [];

        foreach ($sistemasCsv as $nombreSistema => $valorAcceso) {
            // Si tiene algún valor asignado en la celda del CSV, lo agregamos a los sistemas
            if (!empty($valorAcceso)) {
                $detalle = new \stdClass();
                $detalle->sistema = (object) ['nombre' => $nombreSistema];
                $detalle->username = $valorAcceso;
                $detalle->rol =  $this->getRol($nombreSistema);
                $detalle->area_aplicacion =  $this->getAreaAplicacion($nombreSistema, $agencia);
                $detalle->motivo = '';

                $data->sistemas[] = $detalle;
            }
        }

        return $data;
    }

    private function getRol($sistema){
        return match ($sistema){
            'Acceso al equipo' => 'Administrador',
            'Autosystem'       => 'Operador',
            'Winsys'           => 'Consulta',
            'Intranet'         => 'Operador',
            'Correo'           => 'Operador',
            default => 'N/A'
        };
    }

    private function getAreaAplicacion($sistema, $agencia){
        return match ($sistema){
            'Acceso al equipo' =>   'No aplica',
            'Autosystem'       => $agencia,
            'Winsys'           => $agencia,
            'Intranet'         => $agencia,
            'Correo'           => $agencia,
            default => 'N/A'
        };
    }

}
