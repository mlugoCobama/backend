<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CambioEstatusSolicitudCompra extends Notification
{
    use Queueable;

    public $solicitud;
    public $nuevoEstatus;




    /**
     * Create a new notification instance.
     */
    public function __construct($solicitud, $nuevoEstatus)
    {
        $this->solicitud = $solicitud;
        $this->nuevoEstatus = $nuevoEstatus;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
       return ['mail', 'database'];

    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        $table = '<table border="1" style="width:100%; border-collapse:collapse;">'; 
        $table .= '<tr><th>Cantidad</th><th>U. Medida</th><th>Descripción</th><th>Observaciones</th></tr>';
        foreach ($this->solicitud->DetallesSolicitud as $detalle) 
        { 
            $table .= '<tr>';
            $table .= '<td>' . $detalle->cantidad . '</td>'; 
            $table .= '<td>' . $detalle->unidadMedida->nombre . '</td>';
            $table .= '<td>' . $detalle->descripcion . '</td>'; 
            $table .= '<td>' . $detalle->observaciones . '</td>'; 
            $table .= '</tr>'; 
        } 
        $table .= '</table>';


        return (new MailMessage)
                    ->subject('Cambio de estatus de solicitud de compra con folio '. $this->solicitud->folio)
                    ->line('El estatus de su solicitud con folio **' . $this->solicitud->folio . '** ha cambiado.')
                    ->line('Nuevo estatus: **' . $this->nuevoEstatus.'**')
                    ->line('Descripcion de la solicitud:')   
                    ->line('**Motivo**: '.$this->solicitud->motivo )
                    ->line('**Detalles**:') 
                    ->line(new \Illuminate\Support\HtmlString($table))

                    // ->action('Ver detalle', url('/modelos/'.$this->modelo->id))
                    ->line('Para cualquier aclaración, comunicate con el area de compras.')
                    ->line('**Gracias.**');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */

    public function toDatabase($notifiable)
    {
        return [
            'modelo_id' => $this->solicitud->id,
            'nombre' => $this->solicitud->folio,
            'nuevo_estatus' => $this->nuevoEstatus,
            'cambiado_por' => auth()->user()->name,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
