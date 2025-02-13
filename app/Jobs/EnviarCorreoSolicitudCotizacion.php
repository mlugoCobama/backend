<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Notifications\SolicitudCotizacionNotification;
use Illuminate\Support\Facades\Notification;

class EnviarCorreoSolicitudCotizacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;

        //
    }

    /**
     * Execute the job.
     */
    public function handle($data): void
    {
        // $this->enviaCorreoProveedores($this->data);
        $proveedores = [$data['0'], $data['1'], $data['2']];
        foreach ($proveedores as $proveedor) {
            //Mail::to($correo)->send(new SolicitudCotizacion($data));
            Notification::route('mail', $proveedor['correo'])->notify(new SolicitudCotizacionNotification($data));
        }
    }
}
