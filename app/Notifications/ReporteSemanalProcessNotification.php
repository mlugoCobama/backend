<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ReporteSemanalProcessNotification extends Notification
{
    use Queueable;

    protected $intercompania;
    protected $nombreEmpresa;
    protected $tipoCompra;
    protected $rutaCompras;
    protected $rutaMacro;

    /**
     * Create a new notification instance.
     */
    public function __construct($intercompania, $nombreEmpresa, $tipoCompra, $rutaCompras, $rutaMacro )
    {
       $this->intercompania = $intercompania;
       $this->nombreEmpresa= $nombreEmpresa;
       $this->tipoCompra= $tipoCompra;
       $this->rutaCompras= $rutaCompras;
       $this->rutaMacro= $rutaMacro;
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
    public function toMail(object $notifiable)
    {
         return (new MailMessage)
            ->subject("REPORTE SEMANAL DE COMPRAS - {$this->nombreEmpresa}")
            ->markdown('emails.reporte_semanal', [])
            ->attach(Storage::path($this->rutaCompras))->attach( Storage::path($this->rutaMacro));


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
