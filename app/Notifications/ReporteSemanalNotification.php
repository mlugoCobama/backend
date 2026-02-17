<?php

namespace App\Notifications;

use App\Exports\ReporteSemanalCambiosCompras;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Compras\Http\Controllers\ReportesComprasController;

class ReporteSemanalNotification extends Notification
{
    use Queueable;

    protected $intercompania;
    protected $nombreEmpresa;
    protected $tipoCompra;

    /**
     * Create a new notification instance.
     */
    public function __construct($intercompania, $nombreEmpresa, $tipoCompra )
    {
       $this->intercompania = $intercompania;
       $this->nombreEmpresa= $nombreEmpresa;
       $this->tipoCompra= $tipoCompra;
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
        $reportes =  new ReportesComprasController();
        $detallesComprasGrales = $reportes->queryReporteSemanal($this->intercompania, 1);    
        $detallesComprasMacro = $reportes->queryReporteSemanal($this->intercompania, 2);
        $fechaCorte = date('d_m_Y');
        $semanaCorte = date('W');
         return (new MailMessage)
            ->subject("REPORTE SEMANAL DE COMPRAS - {$this->nombreEmpresa}")
            ->markdown('emails.reporte_semanal', [])
            ->attachData(
                Excel::raw(new ReporteSemanalCambiosCompras( $detallesComprasMacro, $this->nombreEmpresa), \Maatwebsite\Excel\Excel::XLSX),
                "REPORTE_COMPRAS_{$this->nombreEmpresa}_MACRO_{$fechaCorte}_{$semanaCorte}.xlsx",
                ['mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            )
            ->attachData(
                Excel::raw(new ReporteSemanalCambiosCompras( $detallesComprasGrales, $this->nombreEmpresa), \Maatwebsite\Excel\Excel::XLSX),
                "REPORTE_COMPRAS_{$this->nombreEmpresa}_GENERALES_{$fechaCorte}_{$semanaCorte}.xlsx",
                ['mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );


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
