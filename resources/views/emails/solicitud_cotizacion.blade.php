@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<span style="font-size: 18px; font-weight: bold; color: #2d3748;">Compras COBAMA</span>
@endcomponent
@endslot

# Solicitud de cotización

Estimado proveedor,

Por medio del presente, el área de compras de **COBAMA** solicita atentamente la cotización de los siguientes insumos para la solicitud con folio: **{{ $data['solicitudCompra']->folio }}**

## Insumos solicitados

<table style="width:100%; border-collapse:collapse; font-size:14px;">
    <thead style="background-color:#f2f2f2;">
        <tr>
            <th style="border:1px solid #ccc; padding:8px;">Cant.</th>
            <th style="border:1px solid #ccc; padding:8px;">U. Medida</th>
            <th style="border:1px solid #ccc; padding:8px;">Descripción</th>
            <th style="border:1px solid #ccc; padding:8px;">Observaciones</th>
            <th style="border:1px solid #ccc; padding:8px;">Img. Ref.</th>
            @if( ($data['solicitudCompra']->tipo == 2) && ($data['solicitudCompra']->usuario_destino == 602))
                <th style="border:1px solid #ccc; padding:8px;">Para el vehículo</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @php $imageIndex = 1; @endphp
        @foreach ($data['detalles'] as $detalle)
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
                @if(($data['solicitudCompra']->tipo == 2) && ($data['solicitudCompra']->usuario_destino == 602))
                    <td style="border:1px solid #ccc; padding:8px;">{{ $detalle['DetalleAutotanque']->DatosVehiculo->marca.' '.$detalle['DetalleAutotanque']->DatosVehiculo->submarca.' '.$detalle['DetalleAutotanque']->DatosVehiculo->modelo}}
                    {{'No. serie: '.$detalle['DetalleAutotanque']->DatosVehiculo->no_serie}} </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>

@if(!empty($data['unidadDestino']))
**IMPORTANTE**
Las insumos solicitados son en referencia al vehículo:
{{ $data['unidadDestino']->marca.' '.$data['unidadDestino']->submarca.' '.$data['unidadDestino']->modelo}}
{{'No. serie: '.$data['unidadDestino']->no_serie}}

@endif

@if (!empty($data['consideraciones']))

**Consideraciones adicionales:**  
{{ $data['consideraciones'] }}
@endif

Para enviar tu cotización o realizar cualquier consulta relacionada, comunícate exclusivamente a los siguientes correos:

@if ($data['solicitudCompra']->tipo == 3 )
- auditor_admon_01@cobama.com.mx
@else
- compras@cobama.com.mx  
- aux_compras@cobama.com.mx
@endif



Este mensaje ha sido enviado desde una dirección no supervisada (no-reply). Por favor, no respondas directamente a este correo.

Gracias por tu atención.  
**Atentamente,**  
Área de Compras - COBAMA

@endcomponent