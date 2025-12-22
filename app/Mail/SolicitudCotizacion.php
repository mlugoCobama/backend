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

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $tipoCorreo = [
            "1" => ['compras@cobama.com.mx', 'aux_compras@cobama.com.mx'],
            "2" => ['compras@cobama.com.mx', 'aux_compras@cobama.com.mx'],
            "3" => ['auditor_admon_01@cobama.com.mx'],
        ];

        $mail = $this->subject('Solicitud de cotización - Folio ' . $this->data['solicitudCompra']->folio)
            ->cc($tipoCorreo[$this->data['solicitudCompra']['tipo'] ])
            ->markdown('emails.solicitud_cotizacion')
            // ->view('emails.solicitud_cotizacion')
            ->with(['data' => $this->data]);

        // Adjuntar imágenes como image_1, image_2, etc.
        // $imageIndex = 1;

        // foreach ($this->data['detalles'] as $detalle) {
        //     if (!empty($detalle['img_referencia'])) {
        //         $formattedPath = str_replace("http://localhost:8000/storage/", "storage/", $detalle['img_referencia']);
        //         $filePath = storage_path('app/public/' . str_replace('storage/', '', $formattedPath));
        //         if (file_exists($filePath)) {
        //             $fileContent = file_get_contents($filePath);
        //             $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        //             $mimeType = match ($extension) {
        //                 'png' => 'image/png',
        //                 'jpg', 'jpeg' => 'image/jpeg',
        //                 'gif' => 'image/gif',
        //                 default => 'application/octet-stream',
        //             };
        //             $mail->attachData($fileContent, 'image_' . $imageIndex . '.' . $extension, [
        //                 'mime' => $mimeType,
        //             ]);
        //             $imageIndex++;
        //         }
        //     }
        // }

        return $mail;
    }
}

