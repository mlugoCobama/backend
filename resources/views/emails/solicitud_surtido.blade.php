@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<span style="font-size: 18px; font-weight: bold; color: #2d3748;">Compras COBAMA</span>
@endcomponent
@endslot

# Solicitud de surtido

Estimado proveedor,

Por medio del presente, el área de compras de **COBAMA** solicita el surtido de la orden de compra con folio: **{{ $datos['ordenCompra']->folio_oc }}** 
de los siguientes insumos:


## Insumos a surtir

<table style="width:100%; border-collapse:collapse; font-size:14px;">
    <thead style="background-color:#f2f2f2;">
        <tr>
            <th style="border:1px solid #ccc; padding:8px;">Cant.</th>
            <th style="border:1px solid #ccc; padding:8px;">U. Medida</th>
            <th style="border:1px solid #ccc; padding:8px;">Descripción</th>
            <th style="border:1px solid #ccc; padding:8px;">Observaciones</th>
            <th style="border:1px solid #ccc; padding:8px;">Img Ref.</th>
        </tr>
    </thead>
    <tbody>
        @php $imageIndex = 1; @endphp
        @foreach ($datos['detalles'] as $detalle)
            <tr>
                <td style="border:1px solid #ccc; padding:8px;">{{ $detalle['cantidad'] }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $detalle['unidadMedida']['nombre'] }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $detalle['descripcion'] }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $detalle['observaciones'] }}</td>
                <td style="border:1px solid #ccc; padding:8px;">
                    @if (!empty($detalle['img_referencia']))
                        <a href="{{ $detalle['img_referencia'] }}">image_{{ $imageIndex }}</a>
                        @php $imageIndex++; @endphp
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@if ($datos['ordenCompra']->modo_pago == 1 || $datos['ordenCompra']->modo_pago == '1' )
> **Importante:** Le solicitamos atentamente que nos envíe las **facturas correspondientes** 
a los insumos detallados en esta orden de compra, a fin de proceder con el proceso administrativo y contable correspondiente.
@endif

**NOTA**:  Esta orden de compra fue generada con base en la cotización previamente solicitada mediante la solicitud de compra con folio **{{ $datos['solicitudCompra']->folio }}** 

Para cualquier aclaración o seguimiento, favor de contactar al área de compras exclusivamente en los siguientes correos:

@if ($datos['solicitudCompra']->tipo == 3 )
- auditor_admon_01@cobama.com.mx
@else
- compras@cobama.com.mx  
- aux_compras@cobama.com.mx
@endif

Este mensaje ha sido enviado desde una dirección no supervisada (no-reply). Por favor, no respondas directamente a este correo.

**Saludos cordiales**,  
**Área de Compras - COBAMA**

@slot('footer')
@component('mail::footer')
COBAMA © {{ date('Y') }}
@endcomponent
@endslot
@endcomponent