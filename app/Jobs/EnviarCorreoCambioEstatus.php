<?php

namespace App\Jobs;

use App\Notifications\CambioEstatusSolicitudCompra;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Compras\Models\SolicitudesCompra;

class EnviarCorreoCambioEstatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idSolicitudCompra;
    protected $estatus;

    public function __construct($idSolicitudCompra, $estatus)
    {
        $this->idSolicitudCompra = $idSolicitudCompra;
        $this->estatus = $estatus;

    }

    public function handle()
    {
        

        $solicitudCompra = SolicitudesCompra::with('DetallesSolicitud.unidadMedida')->findOrFail($this->idSolicitudCompra);
        if($solicitudCompra->empresa != '333'){
            if($solicitudCompra->tipo != 2){
                $correos = DB::connection('intranet')->select('call SOPORTEZM.SP_GetGereneciaEmpresas(?)', [$solicitudCompra->empresa]);
            }else{
                $correos = DB::connection('intranet')->select('call SOPORTEZM.SP_GetGereneciaEmpresasMacro(?)', [$solicitudCompra->empresa]);
            }
            

            foreach ($correos as $correo) {
                
                Notification::route('mail', $correo->name)->notify(new CambioEstatusSolicitudCompra($solicitudCompra, $this->estatus));
            }
        }
        
    }

}

