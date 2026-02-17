<?php

namespace App\Console\Commands;
use App\Notifications\ReporteSemanalNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SendReporteSemanalComprasEmpresa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-reporte-semanal-compras-empresa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
        {   
            $exepciones = explode(',', env('EXCEPCIONES_REPORTE_COMPRAS'));
            
            $empresas = [
                        333 => 'CORPORACION ADMINISTRATIVA DEL SUR', 201 => 'AGRUPAMIENTO',
                        131 => 'AZTECA GAS', 130 => 'SATELITE GAS', 251 => 'FLAMAMEX',
                        210 => 'REYES GAS', 155 => 'GASAMEX', 135 => 'SEGAS', 110 => 'GARZA GAS',
                        111 => 'GARZA SUR', 250 => 'GAS FLAMAZUL', 132 => 'GAS PREMIO',
                        200 => 'TANQUES SONI', 119 => 'TANQUES GARZA GAS', 190 => 'ZUGAS',
                        133 => 'GASERA MULTIREGIONAL', 353 => 'GAS URBANO', 710 => 'NISSAN UNIVERSIDAD',
                        7051 => 'NISSAN AZCAPOTZALCO', 712 => 'NISSAN CAMPESTRE', 700 => 'CORPORATIVO AUTOS SONI',
                        240 => 'SERVIGAS DEL VALLE', 2000 => 'SERVICIO EL ONCE', 7064 => 'RENAULT AZCAPOTZALCO',
                        7062 => 'RENAULT ECATEPEC', 7063 => 'RENAULT VALLEJO', 7061 => 'RENAULT PACHUCA',
                        191 => 'BARAGAS', 354 => 'IZTAGAS Y ENERGIA', 353111 => 'GAS URBANO - GARZA SUR', 251250 => 'FLAMAMEX - FLAMAZUL'
                ];

                foreach ($empresas as $intercompania => $nombre) {
                    $isDisabled = in_array($intercompania, $exepciones );
                    if(!$isDisabled){
                        $correos = DB::connection('intranet')->select('call SOPORTEZM.SP_GetGereneciaEmpresas(?)', [$intercompania]);
                        if(!empty($correos)){
                            foreach ($correos as $correo) {
                                Notification::route('mail', $correo->name)->notify( new ReporteSemanalNotification($intercompania, $nombre, 1));
                            }
                        } 
                    }
                }
        }

}
