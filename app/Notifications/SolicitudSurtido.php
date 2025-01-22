<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SolicitudSurtido extends Notification
{
    use Queueable;
    public $datos;

    /**
     * Create a new notification instance.
     */
    public function __construct($datos)
    {
        $this->datos= $datos;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $table = '<table border="1" style="width:100%; border-collapse:collapse;">'; 
        $table .= '<tr><th>Cantidad</th><th>Unidad de Medida</th><th>Descripción</th><th>Observaciones</th><th>Imagen de Referencia</th></tr>';
        foreach ($this->datos['detalles'] as $detalle) 
        { 
            $table .= '<tr>';
            $table .= '<td>' . $detalle['cantidad'] . '</td>'; 
            $table .= '<td>' . $detalle['unidadMedida']['nombre'] . '</td>';
            $table .= '<td>' . $detalle['descripcion'] . '</td>'; 
            $table .= '<td>' . $detalle['observaciones'] . '</td>'; 
            $table .= '<td><a href="' . $detalle['img_referencia'] . '">Ver Imagen</a></td>'; 
            $table .= '</tr>'; 
        } 
        $table .= '</table>'; 
    
        $mailMessage = (new MailMessage)
            ->subject('Solicitud de surtido')
            ->line(new \Illuminate\Support\HtmlString('<h1>Solicitud de surtido</h1>'))
            ->line('El área de compras COBAMA solicita el surtido de lo siguiente:') 
            ->line('Detalles:') 
            ->line(new \Illuminate\Support\HtmlString($table))
            ->line('Saludos cordiales');
    
        foreach ($this->datos['detalles'] as $detalle) {
            if (!is_null($detalle['img_referencia'])) {
                $formattedPath = str_replace("http://localhost:8000/storage/", "storage/", $detalle['img_referencia']);
                $filePath = storage_path('app/public/' . str_replace('storage/', '', $formattedPath));
                $fileContent = file_get_contents($filePath);
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $mimeType = match($extension) {
                    'png' => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    default => 'application/octet-stream',
                };
                $mailMessage->attachData($fileContent, 'img_ref_' . $detalle['descripcion'] . '.' . $extension, [
                    'mime' => $mimeType,
                ]);
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
