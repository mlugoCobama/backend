<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class SolicitudCotizacion extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $empresa;

    public function __construct($data, $empresa)
    {
        $this->data = $data;
        $this->empresa = $empresa;
    }

    public function build()
    {
        $tipoCorreo = [
            "1" => ['compras@cobama.com.mx', 'aux_compras@cobama.com.mx'],
            "2" => ['compras@cobama.com.mx', 'aux_compras@cobama.com.mx'],
            "3" => ['auditor_admon_01@cobama.com.mx'],
            "4" => ['auditor_admon_01@cobama.com.mx'],
        ];

        $mail = $this->subject('Solicitud de cotización - Folio ' . $this->data['solicitudCompra']->folio )
            ->cc($tipoCorreo[$this->data['solicitudCompra']['tipo'] ])
            ->markdown('emails.solicitud_cotizacion')
            ->with(['data' => $this->data, 'empresa' => $this->empresa]);

        return $mail;
    }
}

