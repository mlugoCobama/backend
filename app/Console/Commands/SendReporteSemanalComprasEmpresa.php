<?php

namespace App\Console\Commands;

use App\Exports\GastosUnidadesDetallesExport;
use Illuminate\Console\Command;
use Modules\Compras\Http\Controllers\ReportesComprasController;

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
            // $intercompanias  = [
            //     131, 130, 251, 210, 155, 135, 110 , 111 ,
            //     250, 132, 119, 190, 133, 353, 191, 354 ,
            // ];
            
            // $reportes =  new ReportesComprasController();
            // $detalles = $reportes->
            // // Generar el archivo exportado
            // $export = new GastosUnidadesDetallesExport(); 
            // $filePath = storage_path('app/reports/reporte_semanal.xlsx');
            // \Maatwebsite\Excel\Facades\Excel::store($export, 'reports/reporte_semanal.xlsx');

            // // Enviar correo con el archivo adjunto
            // \Mail::to('destinatario@ejemplo.com')->send(new \App\Mail\WeeklyReportMail($filePath));

            // $this->info('Reporte semanal enviado correctamente.');
        }
}
