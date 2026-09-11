<?php

namespace Modules\Ucoip\Services;

use setasign\Fpdi\Fpdi;

class AuditoriaUcoipPdfService
{
    private array $primaryColor = [42, 48, 66];
    private array $textColor = [50, 50, 50];
    private array $borderColor = [210, 210, 210];

    /*
    | GENERAR AUDITORÍA
    */

    public function generarAuditoria($auditoria): string
    {
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        $pdf->setSourceFile(base_path('Modules/Ucoip/resources/assets/Formatos/Blank.pdf'));

        $template = $pdf->importPage(1);
        $pdf->useImportedPage($template);

        /*
        | Encabezado
        */
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(...$this->primaryColor);
        $pdf->Cell(0,10,$this->encode('AUDITORÍA DE UCOIP'),0,1,'C');
        $pdf->SetDrawColor(...$this->primaryColor);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10,$pdf->GetY()+2,200,$pdf->GetY()+2);
        $pdf->Ln(3);

        /*
        | Datos generales
        */

        $this->tituloSeccion($pdf,'DATOS GENERALES');
        $ucoip = $auditoria->ucoip;
        $usuario = trim( ($ucoip->userGlpi?->firstname ?? '') .' ' .($ucoip->userGlpi?->realname ?? ''));

        $empresa = $ucoip->empresa?->nombre ?? 'N/A';
        $this->datoGeneral($pdf,'Usuario:',$usuario ?: 'N/A','UCOIP:',$ucoip->ucoip ?? 'N/A');
        $this->datoGeneral($pdf,'Empresa:',$empresa,'Fecha:',$auditoria->fecha?->format('d/m/Y H:i') ?? 'N/A');
        $this->datoGeneral($pdf,'Auditor:',$auditoria->responsable?->name ?? 'N/A','','');
        $pdf->Ln(3);

        /*
        | Detalles
        */

        $hardware = $auditoria->detalles->where('tipo', 'hardware');
        $sistemas = $auditoria->detalles ->where('tipo', 'sistema');
        $recursos = $auditoria->detalles->where('tipo', 'recurso_red');
        $licencias = $auditoria->detalles->where('tipo', 'licenciamiento');
        $tokens = $auditoria->detalles->where('tipo', 'token');

        /*
        | Hardware
        */

        if ($hardware->isNotEmpty()) {
            $this->tituloSeccion($pdf,'ACTIVOS AUDITADOS');
            $this->tablaHardwareAuditoria($pdf,$hardware
            );
            $pdf->Ln(3);
        }

        /*
        | Sistemas
        */

        if ($sistemas->isNotEmpty()) {
            $this->tituloSeccion($pdf,'SISTEMAS / APLICACIONES');
            $this->tablaSistemasAuditoria($pdf,$sistemas
            );
            $pdf->Ln(3);
        }

        /*
        | Recursos de red
        */

        if ($recursos->isNotEmpty()) {
            $this->tituloSeccion($pdf,'RECURSOS DE RED');
            $this->tablaRecursosAuditoria($pdf,$recursos
            );
            $pdf->Ln(3);
        }

        /*
        | Licenciamientos
        */

        if ($licencias->isNotEmpty()) {
            $this->tituloSeccion($pdf,'LICENCIAMIENTOS');
            $this->tablaLicenciamientosAuditoria($pdf,$licencias
            );
            $pdf->Ln(3);
        }

        /*
        | Tokens

        */

        if ($tokens->isNotEmpty()) {
            $this->tituloSeccion($pdf,'Tokens');
            $this->tablaTokensAuditoria($pdf,$tokens
            );
            $pdf->Ln(3);
        }

        /*

        | Observaciones generales

        */

        $this->verificarEspacioEnPagina( $pdf,40);
        $this->tituloSeccion($pdf,'OBSERVACIONES GENERALES');
        $pdf->SetDrawColor(...$this->borderColor);
        $pdf->SetLineWidth(0.2);

        for ($i = 0; $i < 2; $i++) {
            $pdf->Cell(190, 5,'','B',1);
        }

        /*

        | Firmas

        */

        // $this->seccionFirmas($pdf);

        /*

        | Guardar

        */

        // $path = storage_path('app/auditorias/auditoria_' .$auditoria->id .'.pdf');

        // if (!is_dir(dirname($path))) {
        //     mkdir(
        //         dirname($path),
        //         0755,
        //         true
        //     );
        // }

        return $pdf->Output('S');

        // $pdf->Output(
        //     'F',
        //     $path
        // );

        // return $path;
    }

    /*
    | GENERAR REVISIÓN
    */

    public function generar($ucoip): string
    {
        $pdf = new Fpdi();

        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $pdf->setSourceFile(
            base_path('Modules/Ucoip/resources/assets/Formatos/Blank.pdf'
            )
        );

        $template = $pdf->importPage(1);
        $pdf->useImportedPage($template);

        /*

        | Encabezado

        */

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(...$this->primaryColor);

        $pdf->Cell(
            0,
            10,
            $this->encode('REVISIÓN DE UCOIP'),
            0,
            1,
            'C'
        );

        $pdf->SetDrawColor(...$this->primaryColor);
        $pdf->SetLineWidth(0.5);

        $pdf->Line(
            10,
            $pdf->GetY(),
            200,
            $pdf->GetY()
        );

        $pdf->Ln(3);

        /*

        | Datos generales

        */

        $this->tituloSeccion(
            $pdf,
            'DATOS GENERALES'
        );

        $usuario = trim(
            ($ucoip->userGlpi?->firstname ?? '') .
            ' ' .
            ($ucoip->userGlpi?->realname ?? '')
        );

        $empresa = $ucoip->empresa?->nombre ?? 'N/A';

        $this->datoGeneral(
            $pdf,
            'Usuario:',
            $usuario ?: 'N/A',
            'UCOIP:',
            $ucoip->ucoip ?? 'N/A'
        );

        $this->datoGeneral(
            $pdf,
            'Empresa:',
            $empresa,
            'Fecha:',
            date('d/m/Y')
        );

        $pdf->Ln(3);

        /*

        | Activos

        */

        if (
            !empty($ucoip->activos) &&
            count($ucoip->activos) > 0
        ) {

            $this->tituloSeccion($pdf,'ACTIVOS ASIGNADOS');

            $this->tablaHardware($pdf,$ucoip->activos);

            $pdf->Ln(3);
        }

        /*

        | Sistemas

        */

        if (
            !empty($ucoip->sistemas) &&
            count($ucoip->sistemas) > 0
        ) {

            $this->tituloSeccion($pdf,'SISTEMAS / APLICACIONES');

            $this->tablaSistemas($pdf,$ucoip->sistemas);

            $pdf->Ln(3);
        }

        /*

        | Recursos de red

        */

        if (
            !empty($ucoip->recursosRed) &&
            count($ucoip->recursosRed) > 0
        ) {

            $this->tituloSeccion($pdf,'RECURSOS DE RED');

            $this->tablaRecursosRed($pdf,$ucoip->recursosRed);

            $pdf->Ln(3);
        }

        /*

        | Licenciamientos

        */

        if (
            !empty($ucoip->licenciamientos) &&
            count($ucoip->licenciamientos) > 0
        ) {

            $this->tituloSeccion($pdf,'LICENCIAMIENTOS');

            $this->tablaLicenciamientos($pdf,$ucoip->licenciamientos);

            $pdf->Ln(3);
        }

        /*

        | Tokens

        */

        if (
            !empty($ucoip->tokens) &&
            count($ucoip->tokens) > 0
        ) {

            $this->tituloSeccion($pdf,'Tokens');

            $this->tablaTokens($pdf,$ucoip->tokens);

            $pdf->Ln(3);
        }

        /*

        | Observaciones generales

        */

        $this->verificarEspacioEnPagina(
            $pdf,
            40
        );

        $this->tituloSeccion(
            $pdf,
            'OBSERVACIONES GENERALES'
        );

        $pdf->SetDrawColor(...$this->borderColor);
        $pdf->SetLineWidth(0.2);

        for ($i = 0; $i < 4; $i++) {

            $pdf->Cell(190,7,'','B',1);
        }

        /*

        | Firmas

        */

        $pdf->Ln(15);

        // $this->seccionFirmas($pdf);

        /*

        | Guardar

        */

        // $path = storage_path(
        //     'app/auditorias/auditoria_ucoip.pdf'
        // );

        // if (!is_dir(dirname($path))) {

        //     mkdir(
        //         dirname($path),
        //         0755,
        //         true
        //     );
        // }

        return $pdf->Output('S');
        // $pdf->Output(
        //     'F',
        //     $path
        // );

        // return $path;
    }

    /*
    | TÍTULO DE SECCIÓN
    */

    private function tituloSeccion(Fpdi $pdf, string $titulo): void {

        $this->verificarEspacioEnPagina($pdf, 15);
        $pdf->SetFont('Arial','B',8);
        $pdf->SetTextColor(...$this->primaryColor);
        $pdf->Cell(0,6,$this->encode($titulo),0,1,'L');
        $pdf->Ln(1);
    }

    /*
    | HEADER DE TABLA
    */

    private function renderHeaderTabla(Fpdi $pdf, array $columnas): void {

        $pdf->SetFont('Arial','B',7);

        $pdf->SetFillColor(...$this->primaryColor);

        $pdf->SetTextColor(255,255,255);

        $pdf->SetDrawColor(...$this->primaryColor);
        foreach ($columnas as $col) {
            $pdf->Cell(  $col['w'], 6, $this->encode($col['t']), 1, 0, 'C', true);
        }
        $pdf->Ln();
        $pdf->SetTextColor(...$this->textColor);
        $pdf->SetDrawColor(...$this->borderColor);
        $pdf->SetLineWidth(0.2);
    }

    /*
    | MULTICELL - FILA GENÉRICA
    */

    private function renderRowMultiCell(
        Fpdi $pdf,
        array $cells,
        array $columnas,
        float $lineHeight = 5
    ): void {

        $padding = 1;
        $fontSize = 8;

        $pdf->SetFont(
            'Arial',
            '',
            $fontSize
        );

        /*

        | Calcular altura necesaria

        */

        $alturas = [];

        foreach ($cells as $cell) {

            $width =
                (float) $cell['w']
                - ($padding * 2);

            $text = $this->encode(
                (string) ($cell['text'] ?? ''));

            $alturas[] =
                $this->calcularAltoMultiCell(
                    $pdf,
                    $width,
                    $text,
                    $lineHeight);
        }

        $altoFila = max(
            $alturas ?: [$lineHeight]
        );

        /*

        | Comprobar salto de página

        */

        if (
            $pdf->GetY() + $altoFila >
            $pdf->GetPageHeight() - 15
        ) {

            $pdf->AddPage();

            /*
             * Importar nuevamente el fondo del formato.
             */
            $pdf->setSourceFile(
                base_path(
                    'Modules/Ucoip/resources/assets/Formatos/Blank.pdf'
                ));

            $template = $pdf->importPage(1);
            $pdf->useImportedPage($template);

            /*
             * Restaurar encabezado de tabla.
             */
            $this->renderHeaderTabla(
                $pdf,
                $columnas);
        }

        $xInicial = $pdf->GetX();
        $yInicial = $pdf->GetY();

        $pdf->SetFont(
            'Arial',
            '',
            $fontSize);

        $pdf->SetTextColor(
            ...$this->textColor);

        $pdf->SetDrawColor(
            ...$this->borderColor);

        /*

        | Renderizar celdas

        */

        foreach ($cells as $cell) {

            $width = (float) $cell['w'];

            $text = $this->encode(
                (string) ($cell['text'] ?? ''));

            $align = $cell['align'] ?? 'L';

            $x = $pdf->GetX();

            /*
             * Borde completo de la celda.
             */
            $pdf->Rect(
                $x,
                $yInicial,
                $width,
                $altoFila);

            /*
             * Posición interior.
             */
            $pdf->SetXY(
                $x + $padding,
                $yInicial + $padding);

            $pdf->MultiCell(
                $width - ($padding * 2),
                $lineHeight,
                $text,
                0,
                $align,
                false);

            /*
             * Regresar al inicio de la fila.
             */
            $pdf->SetXY(
                $x + $width,
                $yInicial);
        }

        /*

        | Posicionar siguiente fila

        */

        $pdf->SetXY(
            $xInicial,
            $yInicial + $altoFila
        );
    }

    /*
    | CALCULAR ALTURA MULTICELL
    */

    private function calcularAltoMultiCell(
        Fpdi $pdf,
        float $width,
        string $text,
        float $lineHeight
    ): float {

        if ($text === '') {
            return $lineHeight + 2;
        }

        $words = preg_split(
            '/\s+/',
            trim($text)
        );

        $lineas = 1;
        $lineaActual = '';

        foreach ($words as $word) {

            $candidata =
                $lineaActual === ''
                    ? $word
                    : $lineaActual . ' ' . $word;

            if (
                $pdf->GetStringWidth(
                    $candidata
                ) <= $width
            ) {

                $lineaActual = $candidata;

                continue;
            }

            $lineas++;
            $lineaActual = $word;
        }

        /*

        | Valores largos sin espacios

        |
        | UUID
        | Número de serie
        | Token
        | IP
        | Etc.
        |
        */

        if (
            $lineaActual !== '' &&
            $pdf->GetStringWidth(
                $lineaActual
            ) > $width
        ) {

            $lineas +=
                (int) ceil(
                    $pdf->GetStringWidth(
                        $lineaActual
                    ) / $width
                ) - 1;
        }

        return max(
            $lineHeight + 2,
            ($lineas * $lineHeight) + 2
        );
    }

    /*
    | TABLA HARDWARE
    */

    private function tablaHardware(
        Fpdi $pdf,
        $activos
    ): void {

        $columnas = [
            ['w' => 30, 't' => 'Tipo'],
            ['w' => 30, 't' => 'Marca'],
            ['w' => 35, 't' => 'Modelo'],
            ['w' => 35, 't' => 'Inventario'],
            ['w' => 35, 't' => 'Serie'],
            ['w' => 25, 't' => 'Evaluación'],
        ];

        $this->renderHeaderTabla(
            $pdf,
            $columnas
        );

        foreach ($activos as $activo) {

            $hardware = $activo->hardware;

            $this->renderRowMultiCell(
                $pdf,
                [
                    [
                        'w' => 30,
                        'text' =>
                            $hardware?->tipoHardware?->tipo ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 30,
                        'text' => $hardware?->marca ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 35,
                        'text' => $hardware?->modelo ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 35,
                        'text' =>
                            $hardware?->no_inventario ?? '',
                        'align' => 'C',
                    ],
                    [
                        'w' => 35,
                        'text' =>
                            $hardware?->no_serie ?? '',
                        'align' => 'C',
                    ],
                    [
                        'w' => 25,
                        'text' => '',
                        'align' => 'C',
                    ],
                ],
                $columnas);
        }
    }

    /*
    | TABLA SISTEMAS
    */

    private function tablaSistemas(
        Fpdi $pdf,
        $sistemas
    ): void {

        $columnas = [
            ['w' => 55, 't' => 'Sistema'],
            ['w' => 45, 't' => 'Usuario'],
            ['w' => 30, 't' => 'Evaluación'],
            ['w' => 60, 't' => 'Observaciones'],
        ];

        $this->renderHeaderTabla(
            $pdf,
            $columnas
        );

        foreach ($sistemas as $sistema) {

            $this->renderRowMultiCell(
                $pdf,
                [
                    [
                        'w' => 55,
                        'text' =>
                            $sistema->sistema?->nombre ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 45,
                        'text' =>
                            $sistema->username ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 30,
                        'text' => '',
                        'align' => 'C',
                    ],
                    [
                        'w' => 60,
                        'text' => '',
                        'align' => 'L',
                    ],
                ],
                $columnas);
        }
    }

    /*
    | TABLA RECURSOS DE RED
    */

    private function tablaRecursosRed(
        Fpdi $pdf,
        $recursos
    ): void {

        $columnas = [
            ['w' => 45, 't' => 'Recurso'],
            ['w' => 55, 't' => 'Valor'],
            ['w' => 30, 't' => 'Evaluación'],
            ['w' => 60, 't' => 'Observaciones'],
        ];

        $this->renderHeaderTabla(
            $pdf,
            $columnas
        );

        foreach ($recursos as $recurso) {

            $this->renderRowMultiCell(
                $pdf,
                [
                    [
                        'w' => 45,
                        'text' =>
                            $recurso->recursoRed?->nombre ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 55,
                        'text' => $recurso->valor ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 30,
                        'text' => '',
                        'align' => 'C',
                    ],
                    [
                        'w' => 60,
                        'text' => '',
                        'align' => 'L',
                    ],
                ],
                $columnas
            );
        }
    }

    /*
    | TABLA LICENCIAMIENTOS
    */

    private function tablaLicenciamientos(
        Fpdi $pdf,
        $licenciamientos
    ): void {

        $columnas = [
            ['w' => 75, 't' => 'Licencia'],
            ['w' => 75, 't' => 'Versión'],
            ['w' => 40, 't' => 'Evaluación'],
        ];

        $this->renderHeaderTabla(
            $pdf,
            $columnas
        );

        foreach ($licenciamientos as $licencia) {

            $this->renderRowMultiCell(
                $pdf,
                [
                    [
                        'w' => 75,
                        'text' =>
                            $licencia->licencia?->licencia ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 75,
                        'text' =>
                            $licencia->licencia?->version ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 40,
                        'text' => '',
                        'align' => 'C',
                    ],
                ],
                $columnas
            );
        }
    }

    /*
    | TABLA TOKENS
    */

    private function tablaTokens(
        Fpdi $pdf,
        $tokens
    ): void {

        $columnas = [
            ['w' => 100, 't' => 'Nro. Token'],
            ['w' => 90, 't' => 'Evaluación'],
        ];

        $this->renderHeaderTabla(
            $pdf,
            $columnas
        );

        foreach ($tokens as $token) {

            $this->renderRowMultiCell(
                $pdf,
                [
                    [
                        'w' => 100,
                        'text' =>
                            $token->token?->token ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 90,
                        'text' => '',
                        'align' => 'C',
                    ],
                ],
                $columnas
            );
        }
    }

    /*
    | TABLA HARDWARE - AUDITORÍA
    */

    private function tablaHardwareAuditoria(
        Fpdi $pdf,
        $detalles
    ): void {

        $columnas = [
            ['w' => 20, 't' => 'Tipo'],
            ['w' => 30, 't' => 'Marca'],
            ['w' => 30, 't' => 'Modelo'],
            ['w' => 35, 't' => 'Serie'],
            ['w' => 25, 't' => 'Evaluación'],
            ['w' => 50, 't' => 'Observaciones'],
        ];

        $this->renderHeaderTabla(
            $pdf,
            $columnas
        );

        foreach ($detalles as $detalle) {

            $datos = $detalle->datos ?? [];

            $this->renderRowMultiCell(
                $pdf,
                [
                    [
                        'w' => 20,
                        'text' => $datos['tipo'] ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 30,
                        'text' => $datos['marca'] ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 30,
                        'text' => $datos['modelo'] ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 35,
                        'text' => $datos['no_serie'] ?? '',
                        'align' => 'C',
                    ],
                    [
                        'w' => 25,
                        'text' =>
                            $this->textoResultado(
                                $detalle->resultado
                            ),
                        'align' => 'C',
                    ],
                    [
                        'w' => 50,
                        'text' =>
                            $detalle->observaciones ?? '',
                        'align' => 'L',
                    ],
                ],
                $columnas
            );
        }
    }

    /*
    | TABLA SISTEMAS - AUDITORÍA
    */

    private function tablaSistemasAuditoria(
        Fpdi $pdf,
        $detalles
    ): void {

        $columnas = [
            ['w' => 55, 't' => 'Sistema'],
            ['w' => 45, 't' => 'Usuario'],
            ['w' => 30, 't' => 'Evaluación'],
            ['w' => 60, 't' => 'Observaciones'],
        ];

        $this->renderHeaderTabla(
            $pdf,
            $columnas
        );

        foreach ($detalles as $detalle) {

            $datos = $detalle->datos ?? [];

            $this->renderRowMultiCell(
                $pdf,
                [
                    [
                        'w' => 55,
                        'text' => $datos['nombre'] ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 45,
                        'text' => $datos['username'] ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 30,
                        'text' =>
                            $this->textoResultado(
                                $detalle->resultado
                            ),
                        'align' => 'C',
                    ],
                    [
                        'w' => 60,
                        'text' =>
                            $detalle->observaciones ?? '',
                        'align' => 'L',
                    ],
                ],
                $columnas
            );
        }
    }

    /*
    | TABLA RECURSOS - AUDITORÍA
    */

    private function tablaRecursosAuditoria(
        Fpdi $pdf,
        $detalles
    ): void {

        $columnas = [
            ['w' => 45, 't' => 'Recurso'],
            ['w' => 55, 't' => 'Valor'],
            ['w' => 30, 't' => 'Evaluación'],
            ['w' => 60, 't' => 'Observaciones'],
        ];

        $this->renderHeaderTabla(
            $pdf,
            $columnas
        );

        foreach ($detalles as $detalle) {

            $datos = $detalle->datos ?? [];

            $this->renderRowMultiCell(
                $pdf,
                [
                    [
                        'w' => 45,
                        'text' => $datos['nombre'] ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 55,
                        'text' => $datos['valor'] ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 30,
                        'text' =>
                            $this->textoResultado(
                                $detalle->resultado
                            ),
                        'align' => 'C',
                    ],
                    [
                        'w' => 60,
                        'text' =>
                            $detalle->observaciones ?? '',
                        'align' => 'L',
                    ],
                ],
                $columnas
            );
        }
    }

    /*
    | TABLA LICENCIAMIENTOS - AUDITORÍA
    */

    private function tablaLicenciamientosAuditoria(
        Fpdi $pdf,
        $detalles
    ): void {

        $columnas = [
            ['w' => 55, 't' => 'Licencia'],
            ['w' => 60, 't' => 'Versión'],
            ['w' => 25, 't' => 'Evaluación'],
            ['w' => 50, 't' => 'Observaciones'],
        ];

        $this->renderHeaderTabla(
            $pdf,
            $columnas
        );

        foreach ($detalles as $detalle) {

            $datos = $detalle->datos ?? [];

            $this->renderRowMultiCell(
                $pdf,
                [
                    [
                        'w' => 55,
                        'text' =>
                            $datos['licencia'] ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 60,
                        'text' =>
                            $datos['version'] ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 25,
                        'text' =>
                            $this->textoResultado(
                                $detalle->resultado
                            ),
                        'align' => 'C',
                    ],
                    [
                        'w' => 50,
                        'text' =>
                            $detalle->observaciones ?? '',
                        'align' => 'L',
                    ],
                ],
                $columnas
            );
        }
    }

    /*
    | TABLA TOKENS - AUDITORÍA
    */

    private function tablaTokensAuditoria(
        Fpdi $pdf,
        $detalles
    ): void {

        $columnas = [
            ['w' => 70, 't' => 'Nro Token'],
            ['w' => 45, 't' => 'Evaluación'],
            ['w' => 75, 't' => 'Observaciones'],
        ];

        $this->renderHeaderTabla($pdf,$columnas);
        foreach ($detalles as $detalle) {
            $datos = $detalle->datos ?? [];
            $this->renderRowMultiCell(
                $pdf,
                [
                    [
                        'w' => 70,
                        'text' =>
                            $datos['nombre_token'] ?? '',
                        'align' => 'L',
                    ],
                    [
                        'w' => 45,
                        'text' =>
                            $this->textoResultado(
                                $detalle->resultado
                            ),
                        'align' => 'C',
                    ],
                    [
                        'w' => 75,
                        'text' =>
                            $detalle->observaciones ?? '',
                        'align' => 'L',
                    ],
                ],
                $columnas
            );
        }
    }

    /*
    | FIRMAS
    */

    private function seccionFirmas(
        Fpdi $pdf
    ): void {

        $this->verificarEspacioEnPagina($pdf,25);
        $pdf->SetFont('Arial','',9);
        $pdf->SetTextColor(...$this->textColor);
        $yAnterior = $pdf->GetY();
        $pdf->Line(20,$yAnterior,85,$yAnterior);
        $pdf->SetXY(20,$yAnterior + 2);
        $pdf->Cell(65,4,$this->encode(    'Firma Auditor / TI'),0,0,'C');
        $pdf->Line(115,$yAnterior,180,$yAnterior);
        $pdf->SetXY(115,$yAnterior + 2);
        $pdf->Cell(65,4,$this->encode(    'Firma Usuario / Conformidad'),0,1,'C');
    }

    /*
    | ESPACIO DISPONIBLE
    */

    private function verificarEspacioEnPagina(
        Fpdi $pdf,
        float $espacioRequerido
    ): void {

        if (
            $pdf->GetY() + $espacioRequerido >
            ($pdf->GetPageHeight() - 15)
        ) {

            $pdf->AddPage();

            $pdf->setSourceFile(
                base_path(
                    'Modules/Ucoip/resources/assets/Formatos/Blank.pdf'
                )
            );

            $template = $pdf->importPage(1);
            $pdf->useImportedPage($template);
        }
    }

    /*
    | RESULTADO
    */

    private function textoResultado(
        ?string $resultado
    ): string {

        return match ($resultado) {

            'correcto' =>
                'CORRECTO',

            'incorrecto' =>
                'INCORRECTO',

            'diferencia' =>
                'DIFERENCIA',

            'no_localizado' =>
                'NO LOCALIZADO',

            'no_asignado' =>
                'NO ASIGNADO',

            'no_aplica' =>
                'NO APLICA',

            default =>
                '',
        };
    }

    /*
    | DATO GENERAL
    */

    private function datoGeneral(Fpdi $pdf,string $label1,string $value1,string $label2,string $value2): void {

        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(25,6,$this->encode($label1),0,0);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(70,6,$this->encode($value1),0,0);
        if ($label2 !== '') {
            $pdf->SetFont('Arial','B',9);
            $pdf->Cell(25,6,$this->encode($label2),0,0);
            $pdf->SetFont('Arial','',9);
            $pdf->Cell(70,6,$this->encode($value2),0,1);
        } else {
            $pdf->Ln();
        }
    }

    /*
    | UTF-8 -> ISO-8859-1
    */

    private function encode(
        ?string $texto
    ): string {

        if (is_null($texto)) {
            return '';
        }

        return iconv(
            'UTF-8',
            'ISO-8859-1//TRANSLIT',
            $texto
        );
    }
}
