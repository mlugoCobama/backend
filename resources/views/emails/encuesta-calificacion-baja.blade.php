
@component('mail::message')

@slot('header')
@component('mail::header', ['url' => config('app.url')])
<span style="font-size: 18px; font-weight: bold; color: #2d3748;">
    Renault
</span>
@endcomponent
@endslot

# Alerta de encuesta

Estimado equipo,

Se ha registrado una encuesta de satisfacción correspondiente a la cita **#{{ $encuesta->cita->folio }}** que contiene una o más respuestas con una **calificación menor a 5**.

Por favor, revisar las siguientes observaciones para dar el seguimiento correspondiente.

@component('mail::panel')
**Fecha de encuesta:** {{ $encuesta->fecha?->format('d/m/Y H:i') }}

**Cita:** #{{ $encuesta->cita->folio }}
@endcomponent

## Respuestas con calificación menor a 5

@foreach ($respuestasBajas as $respuesta)

### Pregunta {{ $respuesta['texto_pregunta'] }}

**Calificación:** {{ $respuesta['puntuacion'] }} / 5

**Motivo:**

> {{ $respuesta['motivo'] ?: 'El cliente no proporcionó un motivo.' }}

@if (!$loop->last)
---
@endif

@endforeach

@component('mail::panel')
**Importante:** Las respuestas anteriores requieren revisión y seguimiento por parte del área correspondiente.
@endcomponent


Este mensaje ha sido enviado desde una dirección no supervisada (no-reply). Por favor, no respondas directamente a este correo.


@slot('footer')
@component('mail::footer')
Renault © {{ date('Y') }}
@endcomponent
@endslot

@endcomponent
