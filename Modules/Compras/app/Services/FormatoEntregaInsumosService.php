<?php

namespace Modules\Compras\Services;

use FPDF;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Models\EntregaTecnicos;
use Modules\Compras\Transformers\UsersResource;

class FormatoEntregaInsumosService
{
    private static $usuariosCache = [];
    /**
     * Genera el reporte PDF de Entrega de Recursos y devuelve el contenido binario.
     *
     * @param array $data Datos estructurados para el reporte
     * @return string Contenido binario del PDF
     */
    public function generateResourceDeliveryReport($idSalida)
    {
        $entrega = EntregaTecnicos::with([
            'movimientosEntrega.entrada.detalle.unidadMedida',
            'movimientosEntrega.almacenProducto.producto.unidadMedida'
        ])->findOrFail($idSalida);

        $usuario_recibe = $this->getNombreUsuario(1, $entrega->tecnico_id);
        $usuario_entrega = $this->getNombreUsuario(1, $entrega->usuario_entrega);

        $detallesFormateados = $entrega->movimientosEntrega->map(function ($movimiento) {


            if ($movimiento->entrada && $movimiento->entrada->detalle) {
                $detalle = $movimiento->entrada->detalle;
                return [
                    'cantidad'      => $movimiento->cantidad ?? 0,
                    'unidad'        => $detalle->unidadMedida->abreviatura ?? 'Pza',
                    'descripcion'   => $detalle->descripcion ?? 'Sin descripción',
                    'observaciones' => $detalle->observaciones ?? '',
                ];
            }


            if ($movimiento->almacenProducto && $movimiento->almacenProducto->producto) {
                $producto = $movimiento->almacenProducto->producto;
                return [
                    'cantidad'      => $movimiento->cantidad ?? 0,
                    'unidad'        => $producto->unidadMedida->abreviatura ?? 'Pza',
                    'descripcion'   => $producto->descripcion_planta ?? $producto->descripcion_proveedor ?? 'Sin descripción',
                    'observaciones' => $movimiento->observaciones ?: ($producto->observaciones ?? ''),
                ];
            }


            return [
                'cantidad'      => $movimiento->cantidad ?? 0,
                'unidad'        => 'Pza',
                'descripcion'   => 'Recurso genérico',
                'observaciones' => $movimiento->observaciones ?? '',
            ];
        });

        $data = [
            'folio'       => $entrega->folio,
            'fecha'       => date('d/m/Y H:i', strtotime($entrega->fecha)),
            'entrego'     => $usuario_entrega['nombre_completo'],
            'puesto_entrego' => $usuario_entrega['puesto'],
            'recibe'      => $usuario_recibe['nombre_completo'],
            'puesto_recibe' => $usuario_recibe['puesto'],
            'detalles'    => $detallesFormateados->toArray(),
            'anotaciones' => 'Entrega correspondiente al folio ' . $entrega->folio
        ];


        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 20);

        $logoPath = base_path('Modules/Compras/resources/assets/logo-cobama.png');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 150, 8, 50, 0);
        }

        // --- ENCABEZADO ---
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode('ENTREGA DE INSUMOS '), 0, 1, 'C');
        // $pdf->SetFont('Arial', '', 10);
        // $pdf->Cell(0, 6, utf8_decode('Sistema de Control de Inventarios y Almacén'), 0, 1, 'C');
        $pdf->Ln(5);

        // --- DATOS GENERALES ---
        $pdf->SetFillColor(245, 245, 245);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25, 7, utf8_decode('Empresa:'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(170, 7, ' ' . 'Aui va el nombre de la empresa', 1, 1, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25, 7, utf8_decode('Folio:'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(70, 7, ' ' . $data['folio'], 1, 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(30, 7, utf8_decode('Fecha:'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(70, 7, ' ' . $data['fecha'], 1, 1, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25, 7, utf8_decode('Entregó:'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(70, 7, ' ' . utf8_decode($data['entrego']), 1, 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(30, 7, utf8_decode('Recibió:'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(70, 7, ' ' . utf8_decode($data['recibe']), 1, 1, 'L');

        $pdf->Ln(10);

        // --- TABLA DE DETALLES ---
        $pdf->SetFont('Arial', 'B', 10);
        #42, 48, 66
        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetTextColor(0, 0, 0);

        $w = [15, 20, 95, 65];

        $pdf->Cell($w[0], 8, utf8_decode('Cant.'), 1, 0, 'C', true);
        $pdf->Cell($w[1], 8, utf8_decode('Unidad'), 1, 0, 'C', true);
        $pdf->Cell($w[2], 8, utf8_decode('Descripción'), 1, 0, 'C', true);
        $pdf->Cell($w[3], 8, utf8_decode('Observaciones'), 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);

        foreach ($data['detalles'] as $item) {
            $pdf->Cell($w[0], 6, $item['cantidad'], 1, 0, 'C', true);
            $pdf->Cell($w[1], 6, utf8_decode($item['unidad']), 1, 0, 'C', true);
            $pdf->Cell($w[2], 6, utf8_decode($item['descripcion']), 1, 0, 'L', true);
            $pdf->Cell($w[3], 6, utf8_decode($item['observaciones']), 1, 1, 'L', true);
        }

        $pdf->Ln(8);

        // --- CUADRO DE ANOTACIONES ---
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, utf8_decode('Anotaciones y Comentarios Generales:'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 18, utf8_decode(''), 1, 'L');

        $pdf->Ln(15);

        // --- SECCIÓN DE FIRMAS ---
        $wFirma = 80;
        $espacioEntreFirmas = 30;

        $pdf->Cell($wFirma, 0, '', 'T', 0, 'C');
        $pdf->Cell($espacioEntreFirmas, 0, '', 0, 0, 'C');
        $pdf->Cell($wFirma, 0, '', 'T', 1, 'C');

        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($wFirma, 5, utf8_decode('ENTREGÓ'), 0, 0, 'C');
        $pdf->Cell($espacioEntreFirmas, 5, '', 0, 0, 'C');
        $pdf->Cell($wFirma, 5, utf8_decode('RECIBIÓ'), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($wFirma, 5, utf8_decode($data['entrego']), 0, 0, 'C');
        $pdf->Cell($espacioEntreFirmas, 5, '', 0, 0, 'C');
        $pdf->Cell($wFirma, 5, utf8_decode($data['recibe']), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell($wFirma, 5, utf8_decode($data['puesto_entrego']), 0, 0, 'C');
        $pdf->Cell($espacioEntreFirmas, 5, '', 0, 0, 'C');
        $pdf->Cell($wFirma, 5, utf8_decode($data['puesto_recibe']), 0, 1, 'C');

        return $pdf->Output('S');
    }

    private function getNombreUsuario($tipoSolicitud, $usuarioId)
    {
        $cacheKey = $tipoSolicitud . '_' . $usuarioId;

        if (isset(self::$usuariosCache[$cacheKey])) {
            return self::$usuariosCache[$cacheKey];
        }

        $usuario = UsersResource::collection(DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(?)', [$usuarioId]));

        if (count($usuario) > 0) {
            $nombreCompleto = trim($usuario[0]->firstname . ' ' . $usuario[0]->realname);
            $resultado = [
                'nombre_completo' => $nombreCompleto,
                'puesto' => $usuario[0]->puesto ?? 'Puesto: ',
                'empresa' => $usuario[0]->empresa ?? 'Empresa: ',
            ];
        } else {
            $resultado = [
                'nombre_completo' => 'No asignado',
                'puesto' =>  'Puesto: ',
                'empresa' => 'Empresa: ',
            ];
        }

        self::$usuariosCache[$cacheKey] = $resultado;
        return $resultado;
    }
}
