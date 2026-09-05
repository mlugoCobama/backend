<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Reporte Volumétrico Mensual</title>

    <style>

        @page {
            margin: 35px 35px 45px 35px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }

        /* =========================
           ENCABEZADO
        ========================= */

        .header {
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
        }

        .header-subtitle {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }

        /* =========================
           INFORMACIÓN GENERAL
        ========================= */

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-table td {
            border: 1px solid #ccc;
            padding: 5px;
        }

        .label {
            font-weight: bold;
            background-color: #f2f2f2;
            width: 25%;
        }

        /* =========================
           SECCIONES
        ========================= */

        .section-title {
            font-size: 13px;
            font-weight: bold;
            background-color: #101331;
            color: white;
            padding: 7px;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        /* =========================
           PRODUCTO
        ========================= */

        .producto-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 8px;
        }

        /* =========================
           RESUMEN
        ========================= */

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .summary-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        .summary-number {
            font-size: 15px;
            font-weight: bold;
        }

        .summary-label {
            font-size: 9px;
            color: #666;
        }

        /* =========================
           TABLAS
        ========================= */

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .data-table th {
            background-color: #e9e9e9;
            border: 1px solid #bbb;
            padding: 6px;
            text-align: left;
        }

        .data-table td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* =========================
           ACLARACIONES
        ========================= */

        .aclaracion {
            margin-bottom: 5px;
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }

        /* =========================
           BITÁCORA
        ========================= */

        .bitacora-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bitacora-table th,
        .bitacora-table td {
            border: 1px solid #ccc;
            padding: 5px;
        }

        /* =========================
           FOOTER
        ========================= */

        .footer {
            position: fixed;
            bottom: -25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #777;
        }

        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

    </style>

</head>

<body>

{{-- ============================================================
     ENCABEZADO
============================================================ --}}

<div class="header">

    <div class="header-title">
        Reporte de control volumétrico - {{ isset($data['FechaYHoraReporteMes']) ? \Carbon\Carbon::parse($data['FechaYHoraReporteMes'])->locale('es')->translatedFormat('F Y') : 'N/A' }}
    </div>

    <div class="header-subtitle">
        {{ $data['DescripcionInstalacion'] ?? 'N/A' }}
    </div>

</div>
{{-- ============================================================
     INFORMACIÓN GENERAL
============================================================ --}}

<div class="section-title">
    Identificación Fiscal y Permisos
</div>

<table class="info-table">
        <tr>
        <td class="label">
            Fecha del reporte
        </td>

        <td colspan="3">
            {{ isset($data['FechaYHoraReporteMes']) ? \Carbon\Carbon::parse($data['FechaYHoraReporteMes'])->format('d/m/Y h:i A') : 'N/A' }}
        </td>

    </tr>
    <tr>

        <td class="label">
            Instalación
        </td>

        <td>
            {{ $data['DescripcionInstalacion'] ?? 'N/A' }}
        </td>

        <td class="label">
            Número de permiso
        </td>

        <td>
            {{ $data['NumPermiso'] ?? 'N/A' }}
        </td>
    </tr>

    <tr>

        <td class="label">
            RFC contribuyente
        </td>

        <td>
            {{ $data['RfcContribuyente'] ?? 'N/A' }}
        </td>

        <td class="label">
            RFC REP. LEGAL
        </td>

        <td>
            {{ $data['RfcRepresentanteLegal'] ?? 'N/A' }}
        </td>

    </tr>

    <tr>



        <td class="label">
            Modalidad
        </td>

        <td>
             {{ $data['Caracter'] ?? 'N/A' }}
             ({{ $data['ModalidadPermiso'] ?? 'N/A' }})
        </td>

        <td class="label">
            Clave instalación
        </td>

        <td >
            {{ $data['ClaveInstalacion'] ?? 'N/A' }}
        </td>

    </tr>



</table>


<div class="section-title">
   Infraestructura Declarada
</div>

<table class="info-table">

        <tr>

        <td class="label">
            Producto declarado
        </td>

        <td colspan="5">
            {{ $data['Producto'][0]['ClaveProducto'] ?? 'N/A' }}
        </td>

    </tr>
    <tr>

        <td class="label">
            Pozos
        </td>

        <td>
            {{ $data['  "NumeroPozos": 0'] ?? 0}}
        </td>

        <td class="label">
            Tanques
        </td>

        <td>
            {{ $data['NumeroTanques'] ?? 0 }}
        </td>

        <td class="label">
            Dispensarios
        </td>

        <td>
            {{ $data['NumeroDispensarios'] ?? 0 }}
        </td>
    </tr>
</table>

{{-- ============================================================
     PRODUCTOS
============================================================ --}}

@foreach($data['Producto'] ?? [] as $producto)
    @php
        $reporte = $producto['ReporteDeVolumenMensual'] ?? [];

        $existencias = $reporte['ControlDeExistencias'] ?? [];

        $recepciones =  $reporte['Recepciones'] ?? [];

        $entregas =  $reporte['Entregas'] ?? [];

        /* * ============================ * RECEPCIONES * ============================ */
        $aclaracionesRecepciones = [ 'sinCfdi' => 0, 'autoconsumo' => 0, 'traspaso' => 0, ];
        $nacionalesRecepciones = $recepciones['Complemento'] ?? [];
        foreach ($nacionalesRecepciones as $comp)
        {
            $aclaracion = $comp['Aclaracion'] ?? null;
           if (!$aclaracion) { continue; }
           $aclaracionLower = strtolower($aclaracion); $volumen = 0;
            if ( isset($comp['VolumenDocumentado']['ValorNumerico']) && $comp['VolumenDocumentado']['ValorNumerico'] !== '' )
             { $volumen = (float) $comp['VolumenDocumentado']['ValorNumerico']; }
            elseif ( isset($comp['Nacional']) && is_array($comp['Nacional']) )
            {
                foreach ($comp['Nacional'] as $nacional)
                {
                    foreach ($nacional['CFDIs'] ?? [] as $cfdi)
                    {
                        $volumen += (float) ( $cfdi['VolumenDocumentado']['ValorNumerico'] ?? 0 );
                    }
                }
            }
            else
            {
                $regex = '/volumen:\s*([\d.]+)|([\d.]+)\s*litros/i';
                if (preg_match($regex, $aclaracion, $match))
                {
                    $volumen = (float) ( $match[1] ?? $match[2] ?? 0 );
                }
            } if ($volumen <= 0) { continue; }
            if ( str_contains($aclaracionLower, 'sin cfdi') || str_contains($aclaracionLower, 'sin cdfi') )
            {
                $aclaracionesRecepciones['sinCfdi'] += $volumen;
            }
            elseif (str_contains($aclaracionLower, 'autoconsumo'))
            {
                $aclaracionesRecepciones['autoconsumo'] += $volumen;
            }
            elseif (str_contains($aclaracionLower, 'traspaso')) {
                 $aclaracionesRecepciones['traspaso'] += $volumen;
            }
        }
        $aclaracionesEntregas = [ 'sinCfdi' => 0, 'autoconsumo' => 0, 'traspaso' => 0, ];
        $nacionalesEntregas = $entregas['Complemento'] ?? [];
        foreach ($nacionalesEntregas as $comp)
        {
            $aclaracion = $comp['Aclaracion'] ?? null;
            if (!$aclaracion) { continue; }
            $aclaracionLower = strtolower($aclaracion); $volumen = 0;
            if ( isset($comp['VolumenDocumentado']['ValorNumerico']) && $comp['VolumenDocumentado']['ValorNumerico'] !== '' )
            {
                $volumen = (float) $comp['VolumenDocumentado']['ValorNumerico'];
            } elseif ( isset($comp['Nacional']) && is_array($comp['Nacional']) )
            {
                foreach ($comp['Nacional'] as $nacional)
                {
                    foreach ($nacional['CFDIs'] ?? [] as $cfdi) {
                        $volumen += (float) ( $cfdi['VolumenDocumentado']['ValorNumerico'] ?? 0 );
                  }
                }
            }
            else {
                    $regex = '/volumen:\s*([\d.]+)|([\d.]+)\s*litros/i';
                    if (preg_match($regex, $aclaracion, $match))
                    {
                        $volumen = (float) ( $match[1] ?? $match[2] ?? 0 );
                    }
                }
            if ($volumen <= 0) { continue; }
            if ( str_contains($aclaracionLower, 'sin cfdi') || str_contains($aclaracionLower, 'sin cdfi') )
            {
                $aclaracionesEntregas['sinCfdi'] += $volumen;
            }
            elseif (str_contains($aclaracionLower, 'autoconsumo')) { $aclaracionesEntregas['autoconsumo'] += $volumen; }
            elseif (str_contains($aclaracionLower, 'traspaso')) { $aclaracionesEntregas['traspaso'] += $volumen; }
        }
    @endphp


    <div class="section-title">
        Resumen
    </div>

    <table class="summary-table">
        <tr>
            <td colspan="2">
                <div class="summary-number">
                    {{ number_format($existencias['VolumenExistenciasMes'] ?? 0,3) }} L
                </div>
                <div class="summary-label">
                    EXISTENCIAS EN MES
                </div>
            </td>
        </tr>
        <tr>

            <td>
                <div class="summary-number">
                    {{ $recepciones['TotalRecepcionesMes'] ?? 0 }} -  {{ $recepciones['TotalDocumentosMes'] ?? 0 }} docs
                </div>
                <div class="summary-label">
                    TOTAL RECEPCIONES
                </div>
            </td>
            <td>
                <div class="summary-number">
                    {{ $entregas['TotalEntregasMes'] ?? 0 }} - {{ $entregas['TotalDocumentosMes'] ?? 0 }} docs
                </div>
                <div class="summary-label">
                    TOTAL ENTREGAS
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="summary-number">
                    {{ number_format($recepciones['SumaVolumenRecepcionMes']['ValorNumerico'] ?? 0, 3) }} L
                </div>
                <div class="summary-label">
                    VOLUMENES RECIBIDOS
                </div>
            </td>
            <td>
                <div class="summary-number">
                    {{ number_format($entregas['SumaVolumenEntregadoMes']['ValorNumerico'] ?? 0, 3) }} L
                </div>
                <div class="summary-label">
                    VOLUMENES ENTREGADOS
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="summary-number">
                    $ {{ number_format( $recepciones['ImporteTotalRecepcionesMensual'] ?? 0, 3 ) }}
                </div>
                <div class="summary-label">
                    IMPORTE RECEPCIONES
                </div>
            </td>
            <td>
                <div class="summary-number">
                    $ {{ number_format( $entregas['ImporteTotalEntregasMes'] ?? 0, 3) }}
                </div>
                <div class="summary-label">
                    IMPORTE ENTREGAS
                </div>
            </td>


        </tr>

    </table>


    <div class="section-title">
       Aclaraciones Recepciones
    </div>

    <table class="summary-table">
        <tr>
            <td colspan="3">
                <div class="summary-number">
                    {{ number_format($aclaracionesRecepciones['sinCfdi'] + $aclaracionesRecepciones['traspaso'] + $aclaracionesRecepciones['autoconsumo'] , 2) }} L
                </div>
                <div class="summary-label">
                    TOTAL ACUMULADO
                </div>
            </td>
        </tr>
        <tr>
            <td>
            <div class="summary-number">
                    {{ number_format($aclaracionesRecepciones['sinCfdi'], 2) }} L
                </div>
                <div class="summary-label">
                    Sin CFDI
                </div>
            </td>
            <td>
                <div class="summary-number">
                    {{ number_format($aclaracionesRecepciones['traspaso'], 2) }} L
                </div>
                <div class="summary-label">
                    TRASPASO
                </div>
            </td>
             <td>
                <div class="summary-number">
                    {{ number_format($aclaracionesRecepciones['autoconsumo'], 2) }} L
                </div>
                <div class="summary-label">
                    AUTOCONSUMO
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">
       Aclaraciones Entregas
    </div>

    <table class="summary-table">
        <tr>
            <td colspan="3">
                <div class="summary-number">
                    {{ number_format($aclaracionesEntregas['sinCfdi'] + $aclaracionesEntregas['traspaso'] + $aclaracionesEntregas['autoconsumo'] , 2) }} L
                </div>
                <div class="summary-label">
                    TOTAL ACUMULADO
                </div>
            </td>
        </tr>
        <tr>
            <td>
            <div class="summary-number">
                    {{ number_format($aclaracionesEntregas['sinCfdi'], 2) }} L
                </div>
                <div class="summary-label">
                    Sin CFDI
                </div>
            </td>
            <td>
                <div class="summary-number">
                    {{ number_format($aclaracionesEntregas['traspaso'], 2) }} L
                </div>
                <div class="summary-label">
                    TRASPASO
                </div>
            </td>
             <td>
                <div class="summary-number">
                    {{ number_format($aclaracionesEntregas['autoconsumo'], 2) }} L
                </div>
                <div class="summary-label">
                    AUTOCONSUMO
                </div>
            </td>
        </tr>

    </table>

@endforeach


<div class="footer">

    Reporte Volumétrico Mensual

</div>

</body>

</html>
