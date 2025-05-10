<?php

namespace Modules\Compras\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class AutorizacionEmail extends Notification
{
    use Queueable;
    public $data;
    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
       $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        //Url del botón de autorizaciones
        $url = URL::signedRoute('api.confirm.accion', ['id' => $this->data['id'], 'campo' => $this->data['campo'], 'necesarias' => $this->data['autoNecesarias'] ]);

        // $url =  route('api.confirm.accion', ['id' => $this->data['id']]);
        $table = '<table border="1" style="width:100%; border-collapse:collapse;">'; 
        $table .= '<tr><th>Cantidad</th><th>U. Medida</th><th>Descripción</th><th>Observaciones</th></tr>';
        foreach ($this->data['DetallesSolicitud'] as $detalle) 
        { 
            $table .= '<tr>';
            $table .= '<td>' . $detalle['cantidad'] . '</td>'; 
            $table .= '<td>' . $detalle['unidadMedida']['nombre']. '</td>';
            $table .= '<td>' . $detalle['descripcion'] . '</td>'; 
            $table .= '<td>' . $detalle['observaciones'] . '</td>'; 
            $table .= '</tr>'; 
        } 
        $table .= '</table>';
        
        $destino = "";
        if($this->data["c_c"] === 0){
            $destino = $this->data['usuario_destino'];
        }else{
            $destino = $this->data['centro_costo'];
        }
        
        $mailMessage =  (new MailMessage)
            ->subject('Solicitud de autorización de compra') 
            ->line('Se solicita una compra por parte de:')
            ->line( $this->data['usuario_solicita'].' ('.$this->data['empresa'].')')
            ->line("Para: ")
            ->line($destino)
            ->line("Motivo: ")
            ->line($this->data['motivo'])
            ->line('Por lo cual es necesario lo siguiente: ')
            ->line(new \Illuminate\Support\HtmlString($table))
            ->line('Da clic en el siguiente botón para autorizar esta solicitud')
            ->action('Autorizo', $url)
            ->line('Saludos cordiales');
        
              foreach ($this->data['DetallesSolicitud'] as $detalle) {
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
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
