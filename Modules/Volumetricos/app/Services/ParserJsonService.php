<?php

namespace Modules\Volumetricos\Services;

use App\Imports\VolumetricosImport;
use Maatwebsite\Excel\Facades\Excel;

class ParserJsonService
{

    public function generateJson($file){
            $import = new VolumetricosImport();
            Excel::import($import, $file);

            // 1. Extraemos los datos base
            $general = $import->generalImport->data;
            $permisos =  $import->permisosImport->data;
            $controlGeneral = $import->controlGeneralImport->data;

            $recepcionesConsolidadas = $import->getTodasLasRecepciones();
            $entregasConsolidadas   = $import->getTodasLasEntregas();

            $existencias = $controlGeneral['ExistenciasMesInmediatoAnterior'];
            $nodoProducto = $this->buildProductoNode($permisos['ClaveProducto'], $existencias , $permisos['FechaYHoraReporteMes'], $recepcionesConsolidadas, $entregasConsolidadas,  $permisos['ComposDePropanoEnGasLP'],$permisos['ComposDeButanoEnGasLP'] );

            // 2. Construimos la estructura exacta del JSON
            $jsonStructure = [
                "Version"                              => (string)('1.0'),
                "RfcContribuyente"                     => $general['RfcContribuyente'] ?? "",
                "RfcRepresentanteLegal"                => $general['RfcRepresentanteLegal'] ?? "",
                "RfcProveedor"                         => $general['RfcProveedor'] ?? "",
                "Caracter"                             => $permisos['Caracter'] ?? "",
                "ModalidadPermiso"                     => $permisos['ModalidadPermiso'] ?? "",
                "NumPermiso"                           => $permisos['NumPermiso'] ?? "",
                "ClaveInstalacion"                     => $permisos['ClaveInstalacion'] ?? "",
                "DescripcionInstalacion"               => $permisos['DescripcionInstalacion'] ?? "",
                "Geolocalizacion"                      => [$permisos['Geolocalizacion']],
                "NumeroPozos"                          => $permisos['NumeroPozos'] ?? 0,
                "NumeroTanques"                        => $permisos['NumeroTanques'] ?? 0,
                "NumeroDuctosEntradaSalida"            => $permisos['NumeroDuctosEntradaSalida'] ?? 0,
                "NumeroDuctosTransporteDistribucion"   => $permisos['NumeroDuctosTransporteDistribucion'] ?? 0,
                "NumeroDispensarios"                   => $permisos['NumeroDispensarios'] ?? 0,
                "FechaYHoraReporteMes"                 => $permisos['FechaYHoraReporteMes'] ?? now()->toIso8601String(),
                "Producto"                             => [$nodoProducto],
                "BitacoraMensual"                      => []

            //    "RawDATA"                               => [
            //        "recepciones" => $recepcionesConsolidadas,
            //        "entregas" => $entregasConsolidadas,

            //    ]
            ];

            return $this->utf8ize($jsonStructure);
    }

    /**
     * Convierte o limpia recursivamente todos los strings a UTF-8 válido.
     */
    private function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            // Convierte secuencias mal formadas a UTF-8 válido
            return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
        }
        return $mixed;
    }

    /**
     * Construye el nodo "Producto" asignando cada fila de Excel a un nodo independiente en Complemento.
     */
    public static function buildProductoNode(
        string $claveProducto,
        float $volumenExistencias,
        string $fechaMedicion,
        array $filasRecepcionesExcel = [],
        array $filasEntregasExcel = [],
        float $propano,
        float $butano,
        string $unidadMedida = 'UM03'
    ): array {
        // 1. PROCESAR NODO RECEPCIONES (Mapeo 1 a 1)
        $colRecepciones = collect($filasRecepcionesExcel);

        $complementosRecepciones = $colRecepciones->map(function ($item) use ($unidadMedida) {
            return self::buildComplementoItem($item, $unidadMedida);
        })->values()->toArray();

        $nodeRecepciones = [
            'TotalRecepcionesMes'     => $colRecepciones->count(),
            'SumaVolumenRecepcionMes' => [
                'ValorNumerico'  => round($colRecepciones->sum(fn($i) => (float)($i['VolumenDocumentado'] ?? 0)), 4),
                'UnidadDeMedida' => $unidadMedida,
            ],
            'TotalDocumentosMes'             => $colRecepciones->pluck('CFDI')->filter()->count(),
            'ImporteTotalRecepcionesMensual' => round($colRecepciones->sum(fn($i) => (float)($i['PrecioVentaOCompraOContrap'] ?? 0)), 4),
            'Complemento'                     => $complementosRecepciones,
        ];

        // 2. PROCESAR NODO ENTREGAS (Mapeo 1 a 1)
        $colEntregas = collect($filasEntregasExcel);

        $complementosEntregas = $colEntregas->map(function ($item) use ($unidadMedida) {
            return self::buildComplementoItem($item, $unidadMedida);
        })->values()->toArray();

        $nodeEntregas = [
            'TotalEntregasMes'       => $colEntregas->count(),
            'SumaVolumenEntregadoMes' => [
                'ValorNumerico'  => round($colEntregas->sum(fn($i) => (float)($i['VolumenDocumentado'] ?? 0)), 4),
                'UnidadDeMedida' => $unidadMedida,
            ],
            'TotalDocumentosMes'          => $colEntregas->pluck('CFDI')->filter()->count(),
            'ImporteTotalEntregasMes' => round($colEntregas->sum(fn($i) => (float)($i['PrecioVentaOCompraOContrap'] ?? 0)), 4),
            'Complemento'                 => $complementosEntregas,
        ];

        // 3. ENSAMBLAR NODO PRODUCTO FINAL

        $entregas = $nodeEntregas['SumaVolumenEntregadoMes']['ValorNumerico'] ?? 0;
        $recepciones = $nodeRecepciones['SumaVolumenRecepcionMes']['ValorNumerico'] ?? 0;

        // abs() convierte cualquier resultado negativo en positivo
        $diferenciaPositiva = abs($entregas - $recepciones);

        $volExistecniasMes = $volumenExistencias - $diferenciaPositiva;

        return [
            'ClaveProducto'           => $claveProducto,
            'ReporteDeVolumenMensual' => [
                'ControlDeExistencias' => [
                    'VolumenExistenciasMes'     => round($volExistecniasMes, 4),
                    'FechaYHoraEstaMedicionMes' => $fechaMedicion,
                ],
                'Recepciones' => $nodeRecepciones,
                'Entregas'    => $nodeEntregas,
            ],
            "ComposDePropanoEnGasLP" => $propano,
            "ComposDeButanoEnGasLP" => $butano,
        ];
    }
    private static function buildComplementoItem(
        array $item,
        string $unidadMedida
    ): array {

        $claveInstalacion = $item['ClaveInstalacion'] ?? '';
        $prefijo = strtoupper(trim(strstr($claveInstalacion, '-', true) ?: $claveInstalacion));
        $tipoComplemento = ($prefijo === 'EXO') ? 'Expendio' : 'Distribucion';

        $cfdi = trim((string)($item['CFDI'] ?? ''));
        $aclaracion = trim((string)($item['Aclaracion'] ?? ''));

        // CASO 1: Existe CFDI
        if ($cfdi !== '') {
            return [

                'TipoComplemento' => $tipoComplemento,
                'Nacional' => [
                    [
                        'RfcClienteOProveedor' => (string)($item['RfcClienteOProveedor'] ?? ''),

                        'NombreClienteOProveedor' => (string)($item['NombreClienteOProveedor'] ?? ''),

                        'CFDIs' => [
                            [
                                'Cfdi' => $cfdi,
                                'TipoCfdi' => (string)($item['TipoCFDI'] ?? 'Ingreso'),
                                'PrecioVentaOCompraOContrap' => (float)($item['PrecioVentaOCompraOContrap'] ?? 0),
                                'FechaYHoraTransaccion' => (string)($item['FechaYHoraTransaccion'] ?? ''),
                                'VolumenDocumentado' => [
                                    'ValorNumerico' => (float) round($item['VolumenDocumentado'] ?? 0, 4),
                                    'UnidadDeMedida' => $unidadMedida,
                                ],
                            ]
                        ]
                    ]
                ]
            ];
        }

        // CASO 2: NO existe CFDI
        return [
            'TipoComplemento' => $tipoComplemento,
            'Aclaracion' => $aclaracion . '. Volumen: ' . (float)round(($item['VolumenDocumentado'] ?? 0)) . ' Litros',
            // 'VolumenDocumentado' => [
            //     'ValorNumerico' =>
            //         ,

            //     'UnidadDeMedida' =>
            //         $unidadMedida,
            // ],
        ];
    }
}
