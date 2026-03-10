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

        //Plantilla PDF: Formato interno de compra
        // $pdf->setSourceFile(__DIR__ . "/../../../../../storage/app/modules/compras/orden_compra/formato_compras_v1.pdf");
        $pdf->setSourceFile(__DIR__ . "/../../../resources/assets/formato_orden_servicio.pdf");
        $template = $pdf->importPage(1);
        $pdf->useImportedPage($template);

        $datosEntrada = $cita->Datos;
        $datosInventario = $cita->Datos->Inventario;
        $trabajosSolicitados = $cita->Datos->trabajosSolicitados;
        $garantias = $cita->Datos->garantias;

        // Fuente

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 7);
        /**
         * Encabezado de entrada
         */
        $pdf->SetXY(33, 31);
        $pdf->Write(0, date("d/m/Y", strtotime($datosEntrada->fecha)));

        $pdf->SetXY(184, 33);
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->Write(0, $datosEntrada->num_entrada);

        /**
         * Datos de la agencia
         */
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(56, 43);
        $pdf->Write(0, 'AGENCIA RENAULT MEX');
        $pdf->SetXY(33, 47);
        $pdf->Write(0, 'Domicilio Conocido C.P. 00000 MEX');
        $pdf->SetXY(30, 51);
        $pdf->Write(0, '000000000');
        $pdf->SetXY(33, 55);
        $pdf->Write(0, '(55) 5555 5555');
        $pdf->SetXY(45, 59.5);
        $pdf->Write(0, 'Lun. a Dom. 9 AM a 6PM');
        $pdf->SetXY(33, 63.5);
        $pdf->Write(0, 'correo@ejemplo.com');

        /**
         * Datos del cliente
         */
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(138, 43);
        $pdf->Write(0, ($cita->nombre . ' ' . $cita->apellido_paterno . ' ' . $cita->apellido_paterno));
        $pdf->SetXY(138, 48);
        $pdf->Write(0, ($cita->domicilio));
        $pdf->SetXY(138, 52);
        $pdf->Write(0, ($cita->rfc));
        $pdf->SetXY(138, 56);
        $pdf->Write(0, ($cita->telefono));
        $pdf->SetXY(138, 60);
        $pdf->Write(0, ($cita->email));

        /**
         * Datos del Vehiculo
         */
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY(26.6, 76);

        $vin = $cita->vin;

        // Recorremos cada carácter y lo imprimimos con un ancho fijo
        foreach (str_split($vin) as $char) {
            $pdf->Cell(3.8, 0, $char, 0, 0);
        }

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(31, 80);
        $pdf->Write(0, ($cita->placas));
        $pdf->SetXY(31, 84);
        $pdf->Write(0, ($cita->modelo));
        $pdf->SetXY(31, 88);
        $pdf->Write(0, ($cita->tipo));

        $pdf->SetXY(71, 80);
        $pdf->Write(0, ($cita->color));
        $pdf->SetXY(71, 84);
        $pdf->Write(0, ($cita->anio));
        $pdf->SetXY(71, 88);
        $pdf->Write(0, ($cita->kilometraje));

        /**
         * Datos del Inventario
         */
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY(120, 88);
        $pdf->Write(0, ($datosInventario->nivel_gasolina  ?? 0).'%' );
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY($this->calXCheck($datosInventario->antena, 43, 48), 104.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->espejo, 43, 48), 107.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->tapones, 43, 48), 111.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->rines, 43, 48), 114.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->tapon_gasolina, 43, 48), 118.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->radio, 43, 48), 122.5);
        $pdf->Write(0, 'X');


        $pdf->SetXY($this->calXCheck($datosInventario->antena, 79, 84), 104.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->espejo, 79, 84), 107.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->tapones, 79, 84), 111.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->rines, 79, 84), 114.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->tapon_gasolina, 79, 84), 118.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->radio, 79, 84), 122.5);
        $pdf->Write(0, 'X');

        $pdf->SetXY($this->calXCheck($datosInventario->cables_corriente, 119, 124), 104.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->gato, 119, 124), 107.5);
        $pdf->Write(0, 'X');
        $pdf->SetXY($this->calXCheck($datosInventario->objetos_valor, 119, 124), 111.5);
        $pdf->Write(0, 'X');

        $pdf->SetXY(103, 114.5);
        $pdf->Write(0, $datosInventario->otros);
        $pdf->SetXY(110, 118);
        $pdf->Write(0, $datosInventario->vestiduras);
        $pdf->SetXY(107, 121);
        $pdf->Write(0, $datosInventario->cristales);
        /**
         * Datos de seguimiento
         */
        

        $pdf->SetXY(162, 104.3);
        $pdf->Write(0, $cita->empleado_id);
        $pdf->SetXY(175, 107.5);
        $fechaFormateada = $this->dividirFecha($cita->fecha);
        $pdf->Write(0, $fechaFormateada['fecha']);
        $pdf->SetXY(175, 111.3);
        $pdf->Write(0, $fechaFormateada['hora']);

        $pdf->SetXY(175, 114.5);
        $pdf->Write(0, 'PENDIENTE');
        $pdf->SetXY(175, 118);
        $pdf->Write(0, 'PENDIENTE');


        /*-----------------------------------------------------
            * Fila de la tabla  de detalles                                    
            -----------------------------------------------------*/
            $pdf->SetFont('Arial', 'B', 5.5);

            $y = 133;

            //Manejo de tabla detalles con multi lineas para textos largos
            foreach ($trabajosSolicitados as $trabajo) {
                $descripcionTrabajo = $trabajo->descripcion;
                $partes = $trabajo->descripcion;

                // Guardar la posición actual
                $pdf->SetXY(20, $y);
                $pdf->Cell(90, 4.5, $descripcionTrabajo, 0, 0, 'C');
                $pdf->Cell(35, 4.5, $partes, 0, 0, 'C');
                $y += 5;
                $pdf->SetY($y); 
            }

            /*-----------------------------------------------------
            * Fila de la tabla  de detalles                                    
            -----------------------------------------------------*/
            $pdf->SetFont('Arial', 'B', 5.5);

            $y = 198;

            //Manejo de tabla detalles con multi lineas para textos largos
            foreach ($garantias as $garantia) {
                $descripcionGarantia = $garantia->descripcion;
                $partes = $garantia->descripcion;
                // Guardar la posición actual
                $pdf->SetXY(20, $y);
                $pdf->Cell(90, 4.5, $descripcionGarantia, 0, 0, 'C');
                $pdf->Cell(25, 4.5, $partes, 0, 0, 'C');
                $y += 5;
                $pdf->SetY($y); 
            }
        

        // Página 2
        $pdf->AddPage();
        $template = $pdf->importPage(2);
        $pdf->useImportedPage($template, 0, 0);

        // Página 3
        $pdf->AddPage();
        $template = $pdf->importPage(3);
        $pdf->useImportedPage($template, 0, 0);

        // Página 4 (ANEXOS)
        $imagenes = $cita->Datos->TestigosFotograficos;

        $cols       = 2;
        $rows       = 3;
        $maxWidth   = 90;
        $maxHeight  = 75;
        $marginX    = 10;
        $marginY    = 30;
        $gapX       = 5;
        $gapY       = 5;
        $imgsPerPage = $cols * $rows; // 6

        $count = 0;
        foreach ($imagenes as $obj) {
            $rutaOriginal = storage_path("app/renault/citas_servicio/$obj->nombre");

            if (!file_exists($rutaOriginal)) {
                continue;
            }

            // Detectar tipo REAL del archivo
            $info = getimagesize($rutaOriginal);
            if (!$info) {
                continue;
            }

            $mimeType = $info['mime'];

            $imagen = match($mimeType) {
                'image/png'  => imagecreatefrompng($rutaOriginal),
                'image/jpeg' => imagecreatefromjpeg($rutaOriginal),
                'image/webp' => imagecreatefromwebp($rutaOriginal),
                'image/gif'  => imagecreatefromgif($rutaOriginal),
                default      => null,
            };

            if (!$imagen) {
                continue;
            }

            // PNG con transparencia → fondo blanco antes de convertir a JPEG
            if ($mimeType === 'image/png') {
                $w     = imagesx($imagen);
                $h     = imagesy($imagen);
                $fondo = imagecreatetruecolor($w, $h);
                $blanco = imagecolorallocate($fondo, 255, 255, 255);
                imagefill($fondo, 0, 0, $blanco);
                imagecopy($fondo, $imagen, 0, 0, 0, 0, $w, $h);
                imagedestroy($imagen);
                $imagen = $fondo;
            }

            // Calcular proporción para que quepa en la celda
            $realW  = imagesx($imagen);
            $realH  = imagesy($imagen);
            $scaleW = $maxWidth  / $realW;
            $scaleH = $maxHeight / $realH;
            $scale  = min($scaleW, $scaleH);
            $drawW  = round($realW * $scale);
            $drawH  = round($realH * $scale);

            // Guardar JPEG temporal
            $tmpPath = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
            imagejpeg($imagen, $tmpPath, 90);
            imagedestroy($imagen);

            // Nueva página cada 6 imágenes
            if ($count % $imgsPerPage === 0) {
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 16);
                $pdf->Cell(0, 10, 'Anexos', 0, 1, 'C');
            }

            // Calcular posición en la cuadrícula
            $posEnPagina = $count % $imgsPerPage;
            $col         = $posEnPagina % $cols;
            $row         = floor($posEnPagina / $cols);

            // Centrar imagen dentro de su celda
            $offsetX = round(($maxWidth  - $drawW) / 2);
            $offsetY = round(($maxHeight - $drawH) / 2);

            $x = $marginX + $col * ($maxWidth  + $gapX) + $offsetX;
            $y = $marginY + $row * ($maxHeight + $gapY) + $offsetY;

            $pdf->Image($tmpPath, $x, $y, $drawW, $drawH, 'JPEG');
            unlink($tmpPath);

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
        // $soloFecha = $carbon->toDateString(); 
        // $soloHora = $carbon->toTimeString(); 
        // También puedes formatear
        $fechaFormateada = $carbon->format('d/m/Y'); // "19/10/2024"
        $horaFormateada = $carbon->format('H:i:s A'); 
        
        return ['fecha' => $fechaFormateada, 'hora' => $horaFormateada];

    }
}
