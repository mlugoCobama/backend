<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudSurtido extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;
    public $pdfContenido;


    /**
     * Create a new message instance.
     */
    public function __construct($datos, $pdfContenido)
    {
        $this->datos = $datos;
        $this->pdfContenido = $pdfContenido;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->subject('Solicitud de surtido de orden de compra - '. $this->datos['ordenCompra']->folio_oc)
            ->cc(['compras@cobama.com.mx', 'aux_compras@cobama.com.mx'])
            ->markdown('emails.solicitud_surtido')
            ->with(['datos' => $this->datos]);

        // Adjuntar imágenes como image_1, image_2, etc.
        $imageIndex = 1;

        foreach ($this->datos['detalles'] as $detalle) {
            if (!empty($detalle['img_referencia'])) {
                $formattedPath = str_replace("http://localhost:8000/storage/", "storage/", $detalle['img_referencia']);
                $filePath = storage_path('app/public/' . str_replace('storage/', '', $formattedPath));
                if (file_exists($filePath)) {
                    $fileContent = file_get_contents($filePath);
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    $mimeType = match ($extension) {
                        'png' => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        default => 'application/octet-stream',
                    };
                    $mail->attachData($fileContent, 'image_' . $imageIndex . '.' . $extension, [
                        'mime' => $mimeType,
                    ]);
                    $imageIndex++;
                }
            }
        }

        if (!empty($this->pdfContenido)) {
        $mail->attachData($this->pdfContenido, 'orden_compra_'.$this->datos['ordenCompra']->folio_oc.'.pdf', [
                'mime' => 'application/pdf',
            ]);
        }



        return $mail;
    }
}


