<?php

namespace Modules\Volumetricos\Services;

use Barryvdh\DomPDF\Facade\Pdf;


class ReporteJsonPdfService
{
    public function generarReportePdfFromJson($dataJson){
        // $data = json_decode($dataJson, true);

            $pdf = Pdf::loadView(
                'volumetricos::reports.mensual',
                [
                    'data' => $dataJson
                ]
            );

            $pdf->setPaper('a4', 'portrait');

            return $pdf;
    }
}
