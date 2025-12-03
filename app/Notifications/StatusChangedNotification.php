<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusChangedNotification extends Notification
{

    protected $order;
    protected $oldStatus;
    protected $newStatus;

    public function __construct($order, $oldStatus, $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
    
    public function via($notifiable)
    {
        return ['database'];  // podrías agregar 'broadcast' si usarás websockets
    }

    public function toDatabase($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => "El estatus de la orden #{$this->order->id} cambió a {$this->newStatus}",
            // otros datos que necesites
        ];
    }
}
