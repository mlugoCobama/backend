@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<span style="font-size: 18px; font-weight: bold; color: #2d3748;">Compras COBAMA</span>
@endcomponent
@endslot



# Requisición de Dispersión de Diésel
@if ($tipo == 's' )
## Se ha generado una solicitud de dispersión de diésel con los siguientes detalles
@else
## Se ha realizado la dispersión de diesel el dia {{ $solicitud['fecha_dispersion'] ?? 'Pendiente' }} con los siguientes detalles
@endif

## Información General

|   -    |   -    |
|--------|--------|
| Empresa | {{ $empresa ?? 'ND' }} |
| Folio | {{ $solicitud['folio'] }} |
| Fecha de Requisición | {{ \Carbon\Carbon::parse($solicitud['fecha'])->format('d/m/Y H:i') }} |
| Solicita | {{ $usuarioSolicita }} de {{ $area }} |
| Periodo | {{ \Carbon\Carbon::parse($solicitud['inicio_periodo'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($solicitud['fin_periodo'])->format('d/m/Y') }} |

## Detalle de Dispersión

<table  style="width:100%; border-collapse:collapse; font-size:14px;">
    <thead>
        <tr>
            <th style="border:1px solid #ccc; padding:8px;">AT</th>
            <th style="border:1px solid #ccc; padding:8px;">PLACAS</th>
            <th style="border:1px solid #ccc; padding:8px;">TARJETA</th>
            <th style="border:1px solid #ccc; padding:8px;">PROXY</th>
            <th style="border:1px solid #ccc; padding:8px;">LITROS</th>
            <th style="border:1px solid #ccc; padding:8px;">PRECIO DIESEL</th>
            <th style="border:1px solid #ccc; padding:8px;">MONTO SEMANAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($solicitud['detalles'] as $detalle)
            <tr>
                <td style="border:1px solid #ccc; padding:8px;">
                    {{ $detalle['vehiculoToka']['vehiculo']['nro_economico'] }}
                </td>
                <td style="border:1px solid #ccc; padding:8px;">
                    {{ $detalle['vehiculoToka']['vehiculo']['placas'] }}
                </td>
                <td style="border:1px solid #ccc; padding:8px;">
                    {{ $detalle['vehiculoToka']['tarjetaToka']['tarjeta'] }}
                </td>
                <td style="border:1px solid #ccc; padding:8px;">
                    {{ $detalle['vehiculoToka']['tarjetaToka']['proxy_number'] }}
                </td>
                <td style="border:1px solid #ccc; padding:8px; text-align: left;" >
                    {{ number_format($detalle['ventas_litros'], 2) }}
                </td>
                <td style="border:1px solid #ccc; padding:8px; text-align: left;" >
                    ${{ number_format($solicitud['precio_combustible'], 2) }}
                </td>
                <td style="border:1px solid #ccc; padding:8px; text-align: left;">
                    ${{ number_format($detalle['monto_solicitado'], 2) }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

## Nota
Este mensaje ha sido enviado desde una dirección no supervisada (no-reply). Por favor, no respondas directamente a este correo.

**Saludos cordiales**
@if ($tipo == 's' )
 {{ $empresa ?? 'ND' }}
@else
## Compras Cobama
@endif

@slot('footer')
@component('mail::footer')
COBAMA © {{ date('Y') }}
@endcomponent
@endslot
@endcomponent