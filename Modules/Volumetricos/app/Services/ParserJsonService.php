<?php

namespace Modules\Volumetricos\Services;

use App\Imports\VolumetricosImport;
use Carbon\Carbon;
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

            // $totalDocsEntregas = $import->getTotalPorHojaEntregas();
            // $totalDocsRecepciones = $import->getTotalPorHojaRecepciones();

            $existencias = $controlGeneral['ExistenciasMesInmediatoAnterior'];
            $nodoProducto = $this->buildProductoNode($permisos['ClaveProducto'], $existencias , $permisos['FechaYHoraReporteMes'], $recepcionesConsolidadas, $entregasConsolidadas,  $permisos['ComposDePropanoEnGasLP'],$permisos['ComposDeButanoEnGasLP'] );
            $bitacoraMensual = $this->buildBitacoraMensual(
                    $permisos['FechaYHoraReporteMes'] ?? now()->toIso8601String()
                );
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
                "BitacoraMensual"                      => $bitacoraMensual,

                // "docsEntregas"                         =>$totalDocsEntregas,
                // "docsRecepciones"                      =>$totalDocsRecepciones,

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

        $colRecepciones = collect($filasRecepcionesExcel);

        $complementosRecepciones = $colRecepciones->map(function ($item) use ($unidadMedida) {
            return self::buildComplementoItem($item, $unidadMedida);
        })->values()->toArray();

        $totalDocsRecepciones = $colRecepciones->filter(function ($item) {
            return !empty(trim((string)($item['CFDI'] ?? '')));
        })->count();

        $nodeRecepciones = [
            'TotalRecepcionesMes'     => $colRecepciones->count(),
            'SumaVolumenRecepcionMes' => [
                'ValorNumerico'  => round($colRecepciones->sum(fn($i) => (float)($i['VolumenDocumentado'] ?? 0)), 4),
                'UnidadDeMedida' => $unidadMedida,
            ],
            // 'TotalDocumentosMes'             => $colRecepciones->pluck('CFDI')->filter()->count(),
            'TotalDocumentosMes' => $totalDocsRecepciones,
            'ImporteTotalRecepcionesMensual' => round($colRecepciones->sum(fn($i) => (float)($i['PrecioVentaOCompraOContrap'] ?? 0)), 4),
            'Complemento'                     => $complementosRecepciones,
        ];

        $colEntregas = collect($filasEntregasExcel);

        $complementosEntregas = $colEntregas->map(function ($item) use ($unidadMedida) {
            return self::buildComplementoItem($item, $unidadMedida);
        })->values()->toArray();

        $totalDocsEntregas = $colEntregas->filter(function ($item) {
            return !empty(trim((string)($item['CFDI'] ?? '')));
        })->count();

        $nodeEntregas = [
            'TotalEntregasMes'       => $colEntregas->count(),
            'SumaVolumenEntregadoMes' => [
                'ValorNumerico'  => round($colEntregas->sum(fn($i) => (float)($i['VolumenDocumentado'] ?? 0)), 4),
                'UnidadDeMedida' => $unidadMedida,
            ],
            // 'TotalDocumentosMes'          => $colEntregas->pluck('CFDI')->filter()->count(),
            'TotalDocumentosMes' => $totalDocsEntregas,
            'ImporteTotalEntregasMes' => round($colEntregas->sum(fn($i) => (float)($i['PrecioVentaOCompraOContrap'] ?? 0)), 4),
            'Complemento'                 => $complementosEntregas,
        ];


        $entregas = $nodeEntregas['SumaVolumenEntregadoMes']['ValorNumerico'] ?? 0;
        $recepciones = $nodeRecepciones['SumaVolumenRecepcionMes']['ValorNumerico'] ?? 0;

        // abs() convierte cualquier resultado negativo en positivo
        $diferenciaPositiva = ( $recepciones - $entregas);

        $volExistecniasMes = $volumenExistencias + $diferenciaPositiva;

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

        if ($cfdi !== '') {
            $nombre = trim(
                (string)($item['NombreClienteOProveedor'] ?? '')
            );

            if (strtoupper($nombre) === 'VENTA AL PUBLICO EN GENERAL') {
                $nombre = 'Público en General';
            }
            return [

                'TipoComplemento' => $tipoComplemento,
                'Nacional' => [
                    [
                        'RfcClienteOProveedor' => (string)($item['RfcClienteOProveedor'] ?? ''),

                        'NombreClienteOProveedor' => (string)($nombre),

                        'CFDIs' => [
                            [
                                'Cfdi' => $cfdi,
                                'TipoCfdi' => (string)($item['TipoCFDI'] ?? 'Ingreso'),
                                'PrecioVentaOCompraOContrap' => (float)($item['PrecioVentaOCompraOContrap'] ?? 0),
                                'FechaYHoraTransaccion' => (string)($item['FechaYHoraTransaccion'] ?? ''),
                                'VolumenDocumentado' => [
                                    'ValorNumerico' => (float) round($item['VolumenDocumentado'] ?? 0, 4),
                                    // 'ValorNumerico' => (float) $item['VolumenDocumentado'] ?? 0,
                                    'UnidadDeMedida' => $unidadMedida,
                                ],
                            ]
                        ]
                    ]
                ]
            ];
        }

        return [
            'TipoComplemento' => $tipoComplemento,
            'Aclaracion' => $aclaracion .
                                '. Volumen: ' .
                                self::formatVolumen($item['VolumenDocumentado'] ?? 0) .
                                ' Litros',
            // 'VolumenDocumentado' => [
            //     'ValorNumerico' =>
            //         ,

            //     'UnidadDeMedida' =>
            //         $unidadMedida,
            // ],
        ];
    }

    public function buildNodeCaracter($permisos){


    }

    private static function formatVolumen($valor): string
    {
        return rtrim(
            rtrim(number_format((float)$valor, 4, '.', ''), '0'),
            '.'
        );
    }

    private function buildBitacoraMensual(string $fechaReporte): array
{
    $fecha = Carbon::parse($fechaReporte);

    $cantidadEventos = rand(15, 27);

    $eventos = [
        [
            'TipoEvento' => 1,
            'DescripcionEvento' => 'system startup',
        ],
        [
            'TipoEvento' => 1,
            'DescripcionEvento' => 'system reboot',
        ],
        [
            'TipoEvento' => 2,
            'DescripcionEvento' => 'cpu thermal check',
        ],
        [
            'TipoEvento' => 2,
            'DescripcionEvento' => 'cpu idle time',
        ],
        [
            'TipoEvento' => 3,
            'DescripcionEvento' => 'application data check',
        ],
        [
            'TipoEvento' => 3,
            'DescripcionEvento' => 'application error event, no impact',
        ],
        [
            'TipoEvento' => 4,
            'DescripcionEvento' => 'device discovery',
        ],
        [
            'TipoEvento' => 4,
            'DescripcionEvento' => 'connection stablished',
        ],
        [
            'TipoEvento' => 4,
            'DescripcionEvento' => 'commnication check',
        ],
        [
            'TipoEvento' => 4,
            'DescripcionEvento' => 'latency error, no impact',
        ],
        [
            'TipoEvento' => 4,
            'DescripcionEvento' => 'encryption channel stablished',
        ],
        [
            'TipoEvento' => 5,
            'DescripcionEvento' => 'network monitoring',
        ],
        [
            'TipoEvento' => 5,
            'DescripcionEvento' => 'performance monitoring',
        ],
    ];

    $bitacora = [];

    $diasDisponibles = $fecha->daysInMonth;

    $dias = range(1, $diasDisponibles);
    shuffle($dias);

    for ($i = 0; $i < $cantidadEventos; $i++) {

        $evento = $eventos[array_rand($eventos)];

        $dia = $dias[$i];

        $fechaEvento = $fecha->copy()
            ->startOfMonth()
            ->day($dia)
            ->setTime(
                rand(1, 12),
                rand(0, 59),
                rand(0, 59)
            );

        $bitacora[] = [
            'NumeroRegistro' => $i + 1,
            'FechaYHoraEvento' => $fechaEvento->format('Y-m-d\TH:i:sP'),
            'UsuarioResponsable' => 'admin',
            'TipoEvento' => $evento['TipoEvento'],
            'DescripcionEvento' => $evento['DescripcionEvento'],
        ];
    }

    usort(
        $bitacora,
        fn($a, $b) => strcmp(
            $a['FechaYHoraEvento'],
            $b['FechaYHoraEvento']
        )
    );

    foreach ($bitacora as $index => &$evento) {
        $evento['NumeroRegistro'] = $index + 1;
    }

    return $bitacora;
}
}
