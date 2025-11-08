<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SolicitudCotizacionNotification extends Notification
{
    use Queueable;
    public $data;
    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
        //
    }
    /** * Get the notification's delivery channels. * * @param mixed $notifiable * @return array */
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /** * Get the mail representation of the notification. {* * @param mixed $notifiable * @return \Illuminate\Notifications\Messages\MailMessage */

    public function toMail($notifiable)
{
    // $table = '<table border="1" style="width:100%; border-collapse:collapse;">'; 
    // $table .= '<tr><th>Cantidad</th><th>Unidad de Medida</th><th>Descripción</th><th>Observaciones</th><th>Imagen de Referencia</th></tr>';
    // foreach ($this->data['detalles'] as $detalle) 
    // { 
    //     $table .= '<tr>';
    //     $table .= '<td>' . $detalle['cantidad'] . '</td>'; 
    //     $table .= '<td>' . $detalle['unidadMedida']['nombre'] . '</td>';
    //     $table .= '<td>' . $detalle['descripcion'] . '</td>'; 
    //     $table .= '<td>' . $detalle['observaciones'] . '</td>'; 
    //     $table .= '<td><a href="' . $detalle['img_referencia'] . '">Ver Imagen</a></td>'; 
    //     $table .= '</tr>'; 
    // } 
    // $table .= '</table>'; 

    // $mailMessage = (new MailMessage)
    //     ->subject('Solicitud de Cotización para compra con folio'.$this->data['solicitudCompra']->folio)
    //     ->line(new \Illuminate\Support\HtmlString('<h1>Solicitud de cotización</h1>'))
    //     ->line('Por medio del presente, el área de compras de COBAMA solicita atentamente la cotización de los siguientes insumos:') 
    //     ->line('Insumos solicitados:') 
    //     ->line(new \Illuminate\Support\HtmlString($table))
    //     ->line(!empty($this->data['consideraciones']) ? 'Consideraciones adicionales para la cotización:  ' . $this->data['consideraciones'] : '' )
        
    //     ->line('Para enviar tu cotización o realizar cualquier consulta relacionada, comunícate exclusivamente a los siguientes correos:')
    //     ->line(new \Illuminate\Support\HtmlString('<ul><li>compras@cobama.com.mx</li><li>aux_compras@cobama.com.mx</li></ul>'))


    //     ->line('Este mensaje ha sido enviado desde una dirección no supervisada. Por favor, no respondas directamente a este correo.')
    //     ->line('Agradecemos tu pronta atención y quedamos atentos a tu propuesta.')
    //     ->salutation('Atentamente, Área de Compras - COBAMA - Saludos cordiales');

    // foreach ($this->data['detalles'] as $detalle) {
    //     if (!is_null($detalle['img_referencia'])) {
    //         $formattedPath = str_replace("http://localhost:8000/storage/", "storage/", $detalle['img_referencia']);
    //         $filePath = storage_path('app/public/' . str_replace('storage/', '', $formattedPath));
    //         $fileContent = file_get_contents($filePath);
    //         $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    //         $mimeType = match($extension) {
    //             'png' => 'image/png',
    //             'jpg', 'jpeg' => 'image/jpeg',
    //             'gif' => 'image/gif',
    //             default => 'application/octet-stream',
    //         };
    //         $mailMessage->attachData($fileContent, 'img_ref_' . $detalle['descripcion'] . '.' . $extension, [
    //             'mime' => $mimeType,
    //         ]);
    //     }
    // }

    // return $mailMessage;
    $table = '<table style="width:100%; border-collapse:collapse; font-family:Arial, sans-serif; font-size:14px;">';
        $table .= '<thead style="background-color:#f2f2f2;">';
        $table .= '<tr>';
        $table .= '<th style="border:1px solid #ccc; padding:8px;">Cant.</th>';
        $table .= '<th style="border:1px solid #ccc; padding:8px;">U. Medida</th>';
        $table .= '<th style="border:1px solid #ccc; padding:8px;">Descripción</th>';
        $table .= '<th style="border:1px solid #ccc; padding:8px;">Observaciones</th>';
        $table .= '<th style="border:1px solid #ccc; padding:8px;">Img. Ref.</th>';
        $table .= '</tr>';
        $table .= '</thead><tbody>';

        $imageIndex = 1;

        foreach ($this->data['detalles'] as $detalle) {
            $table .= '<tr>';
            $table .= '<td style="border:1px solid #ccc; padding:8px;">' . $detalle['cantidad'] . '</td>';
            $table .= '<td style="border:1px solid #ccc; padding:8px;">' . $detalle['unidadMedida']['nombre'] . '</td>';
            $table .= '<td style="border:1px solid #ccc; padding:8px;">' . $detalle['descripcion'] . '</td>';
            $table .= '<td style="border:1px solid #ccc; padding:8px;">' . $detalle['observaciones'] . '</td>';

            if (!is_null($detalle['img_referencia']) && !empty($detalle['img_referencia'])) {
                $table .= '<td style="border:1px solid #ccc; padding:8px;"><a href="' . $detalle['img_referencia'] . '">image_' . $imageIndex . '</a></td>';
            } else {
                $table .= '<td style="border:1px solid #ccc; padding:8px;">—</td>';
            }

            $table .= '</tr>';
            $imageIndex++;
        }

        $table .= '</tbody></table>';

        $mailMessage = (new MailMessage)
            ->subject('Solicitud de cotización - Folio ' . $this->data['solicitudCompra']->folio)
            ->line(new HtmlString('<p>Estimado proveedor,</p>'))
            ->line('Por medio del presente, el área de compras de COBAMA solicita atentamente la cotización de los siguientes insumos para la solicitud con folio :'. $this->data['solicitudCompra']->folio)
            ->line(new HtmlString('<p><strong>Insumos solicitados:</strong></p>'))
            ->line(new HtmlString($table));

        if (!empty($this->data['consideraciones'])) {
            $mailMessage->line('Consideraciones adicionales: ' . $this->data['consideraciones']);
        }

        $mailMessage
            ->line('Para enviar tu cotización o realizar cualquier consulta relacionada, comunícate exclusivamente a los siguientes correos:')
            ->line(new HtmlString('<ul><li>compras@cobama.com.mx</li><li>aux_compras@cobama.com.mx</li></ul>'))
            ->line('Agradecemos tu pronta atención y quedamos atentos a tu propuesta.')
            ->line('Este mensaje ha sido enviado desde una dirección no supervisada (no-reply). Por favor, no respondas directamente a este correo.')
            ->salutation('Atentamente, Área de Compras - COBAMA - Saludos Cordiales');

        // Adjuntar imágenes con nombre image_1, image_2, etc.
        $imageIndex = 1;

        foreach ($this->data['detalles'] as $detalle) {
            if (!is_null($detalle['img_referencia'])) {
                $formattedPath = str_replace("http://localhost:8000/storage/", "storage/", $detalle['img_referencia']);
                $filePath = storage_path('app/public/' . str_replace('storage/', '', $formattedPath));
                if (file_exists($filePath)) {
                    $fileContent = file_get_contents($filePath);
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    $mimeType = match($extension) {
                        'png' => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        default => 'application/octet-stream',
                    };
                    $mailMessage->attachData($fileContent, 'image_' . $imageIndex . '.' . $extension, [
                        'mime' => $mimeType,
                    ]);
                    $imageIndex++;
                }
            }
        }

        return $mailMessage;

}





    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
