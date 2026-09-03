<?php

namespace Modules\Renault\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use setasign\Fpdi\Fpdi;


class OrdenServicioPdfController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('renault::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('renault::create');
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
        return view('renault::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('renault::edit');
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

    public function OrdenServicioFormatoInterno($cita)
    {
        //JSON de donde se obtienen los datos de facturacion
        $pdf = new Fpdi();
        $pdf->AddPage('P', 'Letter');

        $pathPdf1 = base_path('Modules/Renault/resources/assets/orden_reparacion.pdf');
        $pathPdf2 = base_path('Modules/Renault/resources/assets/formato_orden_servicio.pdf');
        // $pathPdf2 = storage_path(__DIR__ . "/../../../resources/assets/formato_orden_servicio.pdf");

        $paginaUno = $pdf->setSourceFile($pathPdf1);
        $template = $pdf->importPage(1);
        $pdf->useImportedPage($template);

        $datosEntrada = $cita->Datos;
        $datosInventario = $cita->Datos->Inventario;
        $trabajosSolicitados = $cita->Datos->trabajosSolicitados;
        $garantias = $cita->Datos->garantias;
        $firma = storage_path( 'app/renault/citas_servicio/'.$cita->folio.'_firma.png');

        // Fuente

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 7);
        /**
         * Encabezado de entrada
         */
        $pdf->SetXY(45, 31);
        $pdf->Write(0, date("d/m/Y", strtotime($datosEntrada->fecha)));

        $pdf->SetXY(168, 31);
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->Write(0, $datosEntrada->num_entrada);

        /**
         * Datos de la agencia
         */
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(63.5, 41.5);
        $pdf->Write(0, 'AGENCIA RENAULT MEX');
        $pdf->SetXY(44, 45.7);
        $pdf->Write(0, 'Domicilio Conocido C.P. 00000 MEX');
        $pdf->SetXY(41, 49.7);
        $pdf->Write(0, '000000000');
        $pdf->SetXY(44, 53.7);
        $pdf->Write(0, '(55) 5555 5555');
        $pdf->SetXY(54, 58.1);
        $pdf->Write(0, 'Lun. a Dom. 9 AM a 6PM');
        $pdf->SetXY(42, 62.3);
        $pdf->Write(0, 'correo@ejemplo.com');

        /**
         * Datos del cliente
         */
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(130, 41.7);
        $pdf->Write(0, ($cita->nombre . ' ' . $cita->apellido_paterno . ' ' . $cita->apellido_paterno));
        $pdf->SetXY(130, 46);
        $pdf->Write(0, ($cita->domicilio));
        $pdf->SetXY(130, 50);
        $pdf->Write(0, ($cita->rfc));
        $pdf->SetXY(130, 54);
        $pdf->Write(0, ($cita->telefono));
        $pdf->SetXY(130, 58);
        $pdf->Write(0, ($cita->email));

        /**
         * Datos del Vehiculo
         */
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY(40.3, 73);

        $vin = $cita->vin;

        // Recorremos cada carácter y lo imprimimos con un ancho fijo
        foreach (str_split($vin) as $char) {
            $pdf->Cell(2.98, 0, $char, 0, 0);
        }

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(43, 78);
        $pdf->Write(0, ($cita->placas));
        $pdf->SetXY(43, 82);
        $pdf->Write(0, ($cita->modelo));
        $pdf->SetXY(43, 86);
        $pdf->Write(0, ($cita->tipo));

        $pdf->SetXY(76, 78);
        $pdf->Write(0, ($cita->color));
        $pdf->SetXY(76, 82);
        $pdf->Write(0, ($cita->anio));
        $pdf->SetXY(76, 86);
        $pdf->Write(0, ($cita->kilometraje));

        /**
         * Datos del Inventario
         */
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY(115, 88);
        $pdf->Write(0, ($datosInventario->nivel_gasolina  ?? 0).'%' );
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY($this->calXCheck($datosInventario->antena, 52.4, 55.4), 101.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->espejo,  52.4, 55.4), 106);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->tapones,  52.4, 55.4), 110);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->rines,  52.4, 55.4), 114);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->tapon_gasolina,  52.4, 55.4), 118.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->radio,  52.4, 55.4), 122.5);
        $pdf->Write(0, 'X');


        $pdf->SetXY($this->calXCheck($datosInventario->encendedor, 82.5, 85.5), 101.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->tapetes, 82.5, 85.5), 106);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->llanta_refaccion, 82.5, 85.5), 110);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->herramientas, 82.5, 85.5), 114);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->reflejantes, 82.5, 85.5), 118.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->extinguidor, 82.5, 85.5), 122.5);
        $pdf->Write(0, 'X');

        $pdf->SetXY($this->calXCheck($datosInventario->cables_corriente, 115.5, 118.5), 101.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->gato, 115.5, 118.5), 106);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->objetos_valor, 115.5, 118.5), 110);
        $pdf->Write(0, 'X');

        $pdf->SetXY(101, 114.5);
        $pdf->Write(0, $datosInventario->otros);
        $pdf->SetXY(106, 118.5);
        $pdf->Write(0, $datosInventario->vestiduras);
        $pdf->SetXY(104, 122.5);
        $pdf->Write(0, $datosInventario->cristales);
        /**
         * Datos de seguimiento
         */


        $pdf->SetXY(155, 98);
        $pdf->Write(0, $cita->empleado_id);
        $pdf->SetXY(165, 102);
        $fechaFormateada = $this->dividirFecha($cita->fecha);
        $pdf->Write(0, $fechaFormateada['fecha']);
        $pdf->SetXY(165, 106.2);
        $pdf->Write(0, $fechaFormateada['hora']);

        $pdf->SetXY(165, 110);
        $pdf->Write(0, 'PENDIENTE');
        $pdf->SetXY(165.2, 114);
        $pdf->Write(0, 'PENDIENTE');


        /*-----------------------------------------------------
            * Fila de la tabla  de detalles
            -----------------------------------------------------*/
            $pdf->SetFont('Arial', 'B', 5.5);

            $y = 134;

            //Manejo de tabla detalles con multi lineas para textos largos
            foreach ($trabajosSolicitados as $trabajo) {
                $descripcionTrabajo = $trabajo->descripcion;
                $partes = $trabajo->partes;

                // Guardar la posición actual
                $pdf->SetXY(37, $y);
                $pdf->Cell(71, 4, $descripcionTrabajo, 0, 0, 'C');
                $pdf->Cell(33, 4, $partes, 0, 0, 'C');
                $y += 4;
                $pdf->SetY($y);
            }

            /*-----------------------------------------------------
            * Fila de la tabla  de detalles
            -----------------------------------------------------*/
            $pdf->SetFont('Arial', 'B', 5.5);

            $y = 201;

            //Manejo de tabla detalles con multi lineas para textos largos
            foreach ($garantias as $garantia) {
                $descripcionGarantia = $garantia->descripcion;
                $partes = $garantia->tiempo;
                // Guardar la posición actual
                $pdf->SetXY(37, $y);
                $pdf->Cell(71.5, 4, $descripcionGarantia,0, 0, 'C');
                $pdf->Cell(20.5, 4, $partes,0, 0, 'C');
                $y += 4;
                $pdf->SetY($y);
            }

            $pdf->Image($firma, 165, 239, 25);


        $paginas23 = $pdf->setSourceFile($pathPdf2);
        // Página 2 - Contrato de prestacion de servicios
        $pdf->AddPage();
        $template = $pdf->importPage(2);
        $pdf->useImportedPage($template, 0, 0);

        $pdf->Image($firma, 137, 234, 50);

        // Página 3 - Aviso de privacidad
        $pdf->AddPage();
        $template = $pdf->importPage(3);
        $pdf->useImportedPage($template, 0, 0);

        // Página 4 (ANEXOS)
        $imagenes = $cita->Datos->TestigosFotograficos;

        $cols       = 1;
        $rows       = 2;

        $maxWidth   = 200;
        $maxHeight  = 100;

        $descHeight = 10;

        $marginX    = 10;
        $marginY    = 30;

        $gapX       = 5;
        $gapY       = 8;

        $imgsPerPage = $cols * $rows; // 6

        $count = 0;

        foreach ($imagenes as $obj) {

            $rutaOriginal = storage_path( "app/renault/citas_servicio/$obj->nombre");

            if (!file_exists($rutaOriginal)) {
                continue;
            }

            // Detectar tipo REAL del archivo
            $info = getimagesize($rutaOriginal);

            if (!$info) {
                continue;
            }

            $mimeType = $info['mime'];

            $imagen = match ($mimeType) {
                'image/png'  => imagecreatefrompng($rutaOriginal),
                'image/jpeg' => imagecreatefromjpeg($rutaOriginal),
                'image/webp' => imagecreatefromwebp($rutaOriginal),
                'image/gif'  => imagecreatefromgif($rutaOriginal),
                default      => null,
            };

            if (!$imagen) {
                continue;
            }

            // PNG con transparencia → fondo blanco
            if ($mimeType === 'image/png') {

                $w = imagesx($imagen);
                $h = imagesy($imagen);

                $fondo = imagecreatetruecolor($w, $h);

                $blanco = imagecolorallocate(
                    $fondo,
                    255,
                    255,
                    255
                );

                imagefill($fondo, 0, 0, $blanco);

                imagecopy(
                    $fondo,
                    $imagen,
                    0,
                    0,
                    0,
                    0,
                    $w,
                    $h
                );

                imagedestroy($imagen);

                $imagen = $fondo;
            }

            // Dimensiones reales
            $realW = imagesx($imagen);
            $realH = imagesy($imagen);

            // Calcular proporción
            $scaleW = $maxWidth / $realW;
            $scaleH = $maxHeight / $realH;

            $scale = min($scaleW, $scaleH);

            $drawW = round($realW * $scale);
            $drawH = round($realH * $scale);

            // Guardar JPEG temporal
            $tmpPath = tempnam(
                sys_get_temp_dir(),
                'img'
            ) . '.jpg';

            imagejpeg(
                $imagen,
                $tmpPath,
                90
            );

            imagedestroy($imagen);

            // Nueva página cada 6 imágenes
            if ($count % $imgsPerPage === 0) {

                $pdf->AddPage();

                $pdf->SetFont(
                    'Arial',
                    'B',
                    16
                );

                $pdf->Cell(
                    0,
                    10,
                    'Anexos',
                    0,
                    1,
                    'C'
                );
            }

            // Posición dentro de la página
            $posEnPagina = $count % $imgsPerPage;

            $col = $posEnPagina % $cols;
            $row = floor($posEnPagina / $cols);

            // Posición base de la celda
            $cellX = $marginX +
                $col * ($maxWidth + $gapX);

            $cellY = $marginY +
                $row * ($maxHeight + $descHeight + $gapY);

            // Centrar imagen horizontalmente
            $offsetX = ($maxWidth - $drawW) / 2;

            // Centrar imagen verticalmente
            // dentro del espacio reservado para imagen
            $offsetY = ($maxHeight - $drawH) / 2;

            $x = $cellX + $offsetX;
            $y = $cellY + $offsetY;

            // Dibujar imagen
            $pdf->Image(
                $tmpPath,
                $x,
                $y,
                $drawW,
                $drawH,
                'JPEG'
            );

            unlink($tmpPath);

            // ==========================================
            // DESCRIPCIÓN
            // ==========================================

            $descripcion = trim($obj->descripcion ?? 'Sin anotaciones');

            if ($descripcion !== '') {

                // Posición debajo de la imagen
                $descY = $cellY + $maxHeight + 2;

                $pdf->SetFont(
                    'Arial',
                    '',
                    8
                );

                $pdf->SetXY(
                    $cellX,
                    $descY
                );

                // Ancho de la descripción
                $pdf->MultiCell(
                    $maxWidth,
                    4,
                    'Anotaciones: '.utf8_decode($descripcion),
                    0,
                    'C'
                );
            }

            $count++;
        }

        return $pdf->Output('S');
        // $pdf->Output();
    }

    private function calXCheck($dato, $si, $no)
    {
        return $dato == 1 ? $si : $no;
    }

    private function dividirFecha($fechaCompleta){
        $carbon = Carbon::parse($fechaCompleta);
        $fechaFormateada = $carbon->format('d/m/Y');
        $horaFormateada = $carbon->format('H:i:s A');
        return ['fecha' => $fechaFormateada, 'hora' => $horaFormateada];
    }
}
