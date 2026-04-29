<?php

namespace App\Jobs;

use App\Exports\ReporteSemanalCambiosCompras;
use App\Notifications\ReporteSemanalProcessNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Compras\Services\ReportesService;

class ProcesarReporteSemanalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60;
    public $timeout = 1000;

    protected $intercompania;
    protected $nombre;

    protected $reportesService;
    /**
     * Create a new job instance.
     */
    public function __construct($intercompania, $nombre, ReportesService $reportesService)
    {
        $this->reportesService = $reportesService;
        $this->intercompania = $intercompania;
        $this->nombre = $nombre;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Iniciando proceso de reporte', ['empresa' => $this->intercompania]);
        $detallesComprasGrales = $this->reportesService->queryReporteSemanal($this->intercompania, 1);    
        $detallesComprasMacro = $this->reportesService->queryReporteSemanal($this->intercompania, 2); 

        $fechaCorte = date('d_m_Y');
        $semanaCorte = date('W');

        $nombreReporteCompras = 'reportes/reporte_semanal_compras_generales_'.$this->nombre.'_'.$fechaCorte.'_'.$semanaCorte.'.xlsx';
        $nombreReporteMacro = 'reportes/reporte_semanal_compras_macrotaller_'.$this->nombre.'_'.$fechaCorte.'_'.$semanaCorte.'.xlsx';

        $archivoComprasGnrales =  Excel::store(new ReporteSemanalCambiosCompras( $detallesComprasGrales, $this->nombre), $nombreReporteCompras );
        $archivoComprasMacro = Excel::store(new ReporteSemanalCambiosCompras( $detallesComprasMacro, $this->nombre), $nombreReporteMacro );


        if ($archivoComprasGnrales && $archivoComprasMacro && Storage::exists($nombreReporteCompras) && Storage::exists($nombreReporteMacro)) {
            try {
                $correos = DB::connection('intranet')->select('call SOPORTEZM.SP_GetGereneciaEmpresas(?)', [$this->intercompania]);
                        if(!empty($correos)){
                            foreach ($correos as $correo) {
                                Notification::route('mail', $correo->name)->notify( new ReporteSemanalProcessNotification($this->intercompania, $this->nombre, 1, $nombreReporteCompras, $nombreReporteMacro ));
                            }
                        } 
                        Log::info('Reporte procesado correctamente', ['empresa' => $this->intercompania]);

                        if (Storage::exists($nombreReporteCompras)) {
                            Storage::delete($nombreReporteCompras);
                        }

                        if (Storage::exists($nombreReporteMacro)) {
                            Storage::delete($nombreReporteMacro);
                        }

                        Log::info('Archivos borrados correctamente', ['empresa' => $this->intercompania]);
            } catch (\Exception $e) {
                Log::error('Error al enviar correo, no se eliminaron archivos', [
                    'empresa' => $this->intercompania,
                    'error'   => $e->getMessage()
                ]);
                //throw $e;
            }
        }
        else {
            throw new \Exception("No se pudo generar uno o ambos reportes");
            Log::warning('No se pudo generar uno o ambos reportes', ['empresa' => $this->intercompania]);
        }


        
    }
}
