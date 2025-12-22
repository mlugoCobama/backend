<?php

namespace App\Helpers;

use App\Jobs\EnviarCorreoCambioEstatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CambioEstatusSolicitudCompra;
use Modules\Compras\Models\SolicitudesCompra;

class NotificationHelper
{
    public static function sendNotificationEstatusChange($idSolicitudCompra, $estatus)
    {
        
          EnviarCorreoCambioEstatus::dispatch($idSolicitudCompra, $estatus);

    }
}