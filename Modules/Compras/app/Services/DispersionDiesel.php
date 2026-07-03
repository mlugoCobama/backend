<?php

namespace Modules\Compras\Services;

use App\Mail\RequisicionDieselMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Compras\Models\SolcitudDiesel;
use Modules\Compras\Transformers\DispersionesTokaResource;

class DispersionDiesel
{
        public function storeSolicitudDiesel($usuarioSolicita, $inicio,  $fin, $precio, $empresa ){
            $nuevaSolicitud = new SolcitudDiesel();
            $nuevaSolicitud->usuario_solicita = $usuarioSolicita;
            $nuevaSolicitud->inicio_periodo = $inicio; 
            $nuevaSolicitud->fin_periodo = $fin; 
            $nuevaSolicitud->precio_combustible = $precio; 
            $nuevaSolicitud->folio = $this->generarFolio(); 
            $nuevaSolicitud->fecha = now();
            $nuevaSolicitud->empresa = $empresa;
            $nuevaSolicitud->save();

            return $nuevaSolicitud;
        }

        public function updateSolicitudDiesel($idSolicitud){
            $solicitud = SolcitudDiesel::find($idSolicitud);
            $solicitud->fecha_dispersion = now();
            $solicitud->estatus = 2; 
            $solicitud->save();

            return $solicitud;
        }

        public function generarFolio()
        {
            $ultimoFolio = SolcitudDiesel::orderBy('id', 'desc')->first();
            if ($ultimoFolio) {
                $numero = (int) str_replace('RDD-', '', $ultimoFolio->folio);
                $nuevoNumero = $numero + 1;
            } else {
                $nuevoNumero = 1;
            }
            return 'RDD-' . str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);
        }

        public function recuperarSolicitudDiesel($id){
            return SolcitudDiesel::with(['detalles.vehiculoToka.vehiculo','detalles.vehiculoToka.tarjetaToka'])->find($id);
        }

        public function notificarDispersion($tipo, $idDispersion){
            $data = $this->recuperarSolicitudDiesel($idDispersion);
            $usuarioSolicita = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $data->usuario_solicita . ')');

            $nombre = $usuarioSolicita[0]->firstname.' '.$usuarioSolicita[0]->realname ?? 'No disponible';
            $area = $usuarioSolicita[0]->area ?? 'No disponible';
            $empresa = $usuarioSolicita[0]->empresa ?? 'No disponible';
            $intercompania = $data->empresa ?? null;

            if($intercompania){
                $gerencias = DB::connection('intranet')->select('call SOPORTEZM.SP_GetGereneciaEmpresasMacro(' . $intercompania . ')');
                foreach ($gerencias as $gerencia) {
                    $destinatario = $gerencia->name;
                    Mail::to($destinatario)->send(new RequisicionDieselMail($data, $nombre, $empresa, $area, $tipo));
                }    
            }
            
            if($tipo == 's'){
                Mail::to('compras@cobama.com.mx')->send(new RequisicionDieselMail($data, $nombre, $empresa, $area, $tipo));
            }
        }

        public function getDispersionesDiesel(){
            $dispersionesPendientes = SolcitudDiesel::where('estatus','1')->latest()->get();
            $dispersionesGuardadas = SolcitudDiesel::where('estatus','2')->latest()->get();
            $dispersionesRealizadas = SolcitudDiesel::where('estatus','3')->latest()->get();
            return [
                'pendientes' => DispersionesTokaResource::collection($dispersionesPendientes),
                'guardadas' => DispersionesTokaResource::collection($dispersionesGuardadas),
                'realizadas' => DispersionesTokaResource::collection($dispersionesRealizadas)
        ];
        }
}