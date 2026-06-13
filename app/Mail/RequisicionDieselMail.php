<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class RequisicionDieselMail extends Mailable
{
    use Queueable, SerializesModels;

    public $solicitud;
    public $empresa;
    public $usuarioSolicita;
    public $area;
    public $tipo;

    /**
     * Create a new message instance.
     */
    public function __construct($solicitud, $usuarioSolicita, $empresa, $area, $tipo)
    {
        $this->solicitud = $solicitud;
        $this->usuarioSolicita = $usuarioSolicita;
        $this->empresa = $empresa;
        $this->area = $area;
        $this->tipo = $tipo;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Requisición de Dispersión de Diésel - ' . $this->solicitud->folio)
            // ->markdown('emails.dispersion_diesel')
            ->markdown('emails.requisicion-diesel')
            
            ->with([
                'solicitud' => $this->solicitud,
                'empresa' => $this->empresa,
                'usuarioSolicita' => $this->usuarioSolicita,
                'area' => $this->area,
                'tipo' => $this->tipo,
            ]);
    }
}