@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<span style="font-size: 18px; font-weight: bold; color: #2d3748;">Compras COBAMA</span>
@endcomponent
@endslot
# Requisición de Dispersión de Diésel

## Información General

| Campo | Valor |
|--------|--------|
| Folio | {{ $solicitud['folio'] }} |
| Empresa | {{ $empresa }} |
| Fecha de Requisición | {{ \Carbon\Carbon::parse($solicitud['fecha'])->format('d/m/Y H:i') }} |
| Periodo | {{ $solicitud['inicio_periodo'] }} al {{ $solicitud['fin_periodo'] }} |
| Precio Diésel | ${{ number_format($solicitud['precio_combustible'], 2) }} |

## Detalle de Dispersión

<table style="width:100%; border-collapse:collapse; font-size:14px;">
    <thead>
        <tr>
            <th style="border:1px solid #ccc; padding:8px;">No. Económico</th>
            <th style="border:1px solid #ccc; padding:8px;">Placas</th>
            <th style="border:1px solid #ccc; padding:8px;">Tarjeta</th>
            <th style="border:1px solid #ccc; padding:8px;">Proxy</th>
            <th style="border:1px solid #ccc; padding:8px;">Litros</th>
            <th style="border:1px solid #ccc; padding:8px;">Precio Diésel</th>
            <th style="border:1px solid #ccc; padding:8px;">Monto Semanal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($solicitud['detalles'] as $detalle)
        
            <tr>
                <td style="border:1px solid #ccc; padding:8px;">
                    {{$detalle['vehiculoToka']['vehiculo']['nro_economico']  ?? 'ND'}}
                </td>
                <td style="border:1px solid #ccc; padding:8px;">
                    {{ $detalle['vehiculoToka']['vehiculo']['placas'] ?? 'ND'}}
                </td>
                <td style="border:1px solid #ccc; padding:8px;">
                    {{ $detalle['vehiculoToka']['tarjetaToka']['tarjeta'] ?? 'ND'}}
                </td>
                <td style="border:1px solid #ccc; padding:8px;">
                    {{ $detalle['vehiculoToka']['tarjetaToka']['proxy_number'] ?? 'ND' }}
                </td>
                <td style="border:1px solid #ccc; padding:8px;">
                    {{ number_format($detalle['ventas_litros'], 2) }}
                </td>
                <td style="border:1px solid #ccc; padding:8px; text-align: left;">
                    ${{ number_format($solicitud['precio_combustible'], 2) }}
                </td>
                <td style="border:1px solid #ccc; padding:8px; text-align: left;">
                    ${{ number_format($detalle['monto_solicitado'], 2) }}
                </td>
            </tr> 
        @endforeach
    </tbody>
</table>

<br>

<strong>Total solicitado:</strong>
${{ number_format(collect($solicitud['detalles'])->sum('monto_solicitado'), 2) }}

@slot('footer')
@component('mail::footer')
COBAMA © {{ date('Y') }}
@endcomponent
@endslot
@endcomponent