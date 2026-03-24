<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\info;

class SolicitudSurtido extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;
    public $pdfContenido;
    public $rutaComPago;


    /**
     * Create a new message instance.
     */
    public function __construct($datos, $pdfContenido, $rutaComPago)
    {
        $this->datos = $datos;
        $this->pdfContenido = $pdfContenido;
        $this->rutaComPago = $rutaComPago;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $tipoCorreo = [
            "1" => ['compras@cobama.com.mx', 'aux_compras@cobama.com.mx'],
            "2" => ['compras@cobama.com.mx', 'aux_compras@cobama.com.mx'],
            "3" => ['auditor_admon_01@cobama.com.mx'],
        ];
        

        $mail = $this->subject('Solicitud de surtido de orden de compra - '. $this->datos['ordenCompra']->folio_oc)
            ->cc($tipoCorreo[$this->datos['solicitudCompra']['tipo'] ])
            ->markdown('emails.solicitud_surtido')
            ->with(['datos' => $this->datos]);

        // Adjuntar imágenes como image_1, image_2, etc.
        // $imageIndex = 1;

        // foreach ($this->datos['detalles'] as $detalle) {
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

        if (!empty($this->pdfContenido)) {
        $mail->attachData($this->pdfContenido, 'orden_compra_'.$this->datos['ordenCompra']->folio_oc.'.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        if(!empty($this->rutaComPago)){
            $this->rutaComPago = storage_path('app/'.$this->rutaComPago);
            if(file_exists($this->rutaComPago)){
                $extension = pathinfo($this->rutaComPago, PATHINFO_EXTENSION);
                $mimeTypes = [
                    'pdf' => 'application/pdf',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                ];
                $fileName = 'comprobante_pago - '.$this->datos['ordenCompra']->folio_oc;
                $mime = $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
                $mail->attach($this->rutaComPago, [
                            'as' => $fileName . $extension,
                            'mime' => $mime,
                        ]);
            }
        }
        



        return $mail;
    }
}


