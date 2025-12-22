<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PagoOrdenCompra extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;
    protected $comprobantePath;
    protected $mensajeExtra;
    protected $asunto;
    protected $fileName;

    public function __construct($datos, $comprobantePath)
    {
        $this->datos = $datos;
        $this->comprobantePath = storage_path('app/'.$comprobantePath);

        switch ($datos['ordenCompra']->modo_pago) {
            case "1" :
                case 1 :
                $this->mensajeExtra = 'En breve recibirá un segundo correo solicitando el surtido de los insumos correspondientes a esta orden de compra';
                $this->asunto = 'Confirmación de pago - próxima solicitud de surtido de orden de compra con folio: '.$datos['ordenCompra']->folio_oc;
                break;
            case ("2"):
                case 2 :
                $this->mensajeExtra = 'Le agradeceremos que nos envíe el complemento de pago correspondiente a esta transacción para fines de conciliación fiscal.';
                $this->asunto = 'Confirmación de pago - Solicitud de complemento de pago de orden de compra con folio: '. $datos['ordenCompra']->folio_oc;
                break;
            default:
                $this->mensajeExtra = null;
                $this->asunto = 'Confirmación de pago - Orden de compra  con folio: '.$datos['ordenCompra']->folio_oc;
        }

        $this->fileName = 'comprobante_pago - '.$datos['ordenCompra']->folio_oc.'.';
    }

    public function build()
    {
        $tipoCorreo = [
            "1" => ['compras@cobama.com.mx', 'aux_compras@cobama.com.mx'],
            "2" => ['compras@cobama.com.mx', 'aux_compras@cobama.com.mx'],
            "3" => ['auditor_admon_01@cobama.com.mx'],
        ];

        $extension = pathinfo($this->comprobantePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];
        $mime = $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';

        return $this->subject($this->asunto)
                    ->cc($tipoCorreo[$this->datos['solicitudCompra']['tipo'] ])
                    ->markdown('emails.pago_orden_compra')
                    ->with([
                        'mensajeExtra' => $this->mensajeExtra,
                        'datos' => $this->datos,
                    ])
                    ->attach($this->comprobantePath, [
                        'as' => $this->fileName . $extension,
                        'mime' => $mime,
                    ]);
    }





    /**
     * Get the message envelope.
     */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Notificacion de Pago Orden Compra',
    //     );
    // }

    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    // /**
    //  * Get the attachments for the message.
    //  *
    //  * @return array<int, \Illuminate\Mail\Mailables\Attachment>
    //  */
    // public function attachments(): array
    // {
    //     return [];
    // }
}
