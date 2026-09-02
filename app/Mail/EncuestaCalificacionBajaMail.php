<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Renault\Models\RenEncuestaCita;

class EncuestaCalificacionBajaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $encuesta;
    public $respuestasBajas;

    public function __construct(
        RenEncuestaCita $encuesta,
        array $respuestasBajas
    ) {
        $this->encuesta = $encuesta;
        $this->respuestasBajas = $respuestasBajas;
    }

    public function build()
    {
        return $this
            ->subject('Alerta: Encuesta con calificaciones menores a 5')
            ->markdown('emails.encuesta-calificacion-baja');
    }
}
