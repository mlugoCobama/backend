@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<span style="font-size: 18px; font-weight: bold; color: #2d3748;">Compras COBAMA</span>
@endcomponent
@endslot

# Confirmación de pago

Estimado proveedor,

Por medio del presente, el área de compras de **COBAMA** le informa que se ha realizado el **pago correspondiente** a la orden de compra con folio: **{{ $datos['ordenCompra']->folio_oc }}**.

Por lo que se adjunta el comprobante de pago correspondiente.


@if ($datos['ordenCompra']->modo_pago == 1 || $datos['ordenCompra']->modo_pago == '1' )
> **Importante:** En breve recibirá un segundo correo solicitando el **surtido de los insumos** correspondientes a esta orden de compra.
@elseif ($datos['ordenCompra']->modo_pago == 2 || $datos['ordenCompra']->modo_pago == '2')
> **Importante:** Le agradeceremos que nos envíe el **complemento de pago** correspondiente a esta transacción para fines de conciliación fiscal.
@endif

**NOTA**: Este pago corresponde a la orden de compra generada con base en la solicitud de compra con folio **{{ $datos['solicitudCompra']->folio }}**.

Para cualquier aclaración o seguimiento, favor de contactar al área de compras exclusivamente en los siguientes correos:

- compras@cobama.com.mx  
- aux_compras@cobama.com.mx

Este mensaje ha sido enviado desde una dirección no supervisada (no-reply). Por favor, no respondas directamente a este correo.

**Saludos cordiales**,  
**Área de Compras - COBAMA**

@slot('footer')
@component('mail::footer')
COBAMA © {{ date('Y') }}
@endcomponent
@endslot
@endcomponent