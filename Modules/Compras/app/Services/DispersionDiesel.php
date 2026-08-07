<?php

namespace Modules\Compras\Services;

use App\Mail\RequisicionDieselMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Compras\Models\ComRecargasVehiculos;
use Modules\Compras\Models\ExhibicionesRecargas;
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

        public function updateSolicitudDiesel($idSolicitud, $status = 2){
            $solicitud = SolcitudDiesel::find($idSolicitud);
            $solicitud->fecha_dispersion = now();
            $solicitud->estatus = $status;
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
            $dispersionesParciales = SolcitudDiesel::where('estatus','4')->latest()->get();

            return [
                'pendientes' => DispersionesTokaResource::collection($dispersionesPendientes),
                'guardadas' => DispersionesTokaResource::collection($dispersionesGuardadas),
                'realizadas' => DispersionesTokaResource::collection($dispersionesRealizadas),
                'parciales' => DispersionesTokaResource::collection($dispersionesParciales),
            ];
        }

        /**
         * Guarda una recarga para un vehículo.
         *
         * @param int $idVehiculo Identificador del vehículo.
         * @param float $montoSolicitado Monto solicitado por la recarga.
         * @param float $ventaLitros Cantidad de litros vendidos.
         * @param int $idSolicitudDiesel Identificador de la solicitud diesel asociada.
         * @param int $idAsignacionToka Identificador de la asignación Toka asociada.
         *
         */
        public function storeRecargaVehiculo( $idVehiculo, $montoSolicitado, $ventaLitros, $idSolicitudDiesel, $idAsignacionToka){
            $newRow = new ComRecargasVehiculos();
            $newRow->vehiculo_id = $idVehiculo;
            $newRow->fecha = now();
            $newRow->monto_solicitado = $montoSolicitado;
            $newRow->ventas_litros = $ventaLitros;
            $newRow->com_solicitud_diesel_id = $idSolicitudDiesel;
            $newRow->com_vehiculos_toka_id = $idAsignacionToka;
            $newRow->save();

            return $newRow;
        }

        /**
         * Actualiza una recarga de vehículo.
         *
         * @param int $idRecarga Identificador de la recarga a actualizar.
         * @param string $estatus Nuevo estado de la recarga (puede ser 'pendiente', 'dispersada' o 'rechazada').
         * @param float|null $montoAutorizado Monto autorizado para la recarga (opcional).
         *
         */
        public function updateRecargaVehiculo($idRecarga, $estatus, $montoAutorizado = null   ){
            $row = ComRecargasVehiculos::find($idRecarga);
            if($row){
                $row->fecha_dispersion = now();
                $row->estatus = $estatus;
                $row->monto_autorizado =  $montoAutorizado ?? $row->monto_autorizado;
                $row->save();
            }
            return $row;
        }


        public function storeExibicion($numero, $saldo, $montoDispersado, $estatus, $idRecarga, $guardada, $notificada, $fecha_dipsersion){
            $dispersion = new ExhibicionesRecargas();
            $dispersion->numero_exhibicion = $numero;
            $dispersion->saldo_actual_previo = $saldo;
            $dispersion->monto_dispersado = $montoDispersado;
            $dispersion->estatus = $estatus;
            $dispersion->com_recargas_vehiculos_id = $idRecarga;
            $dispersion->guardada = $guardada;
            $dispersion->notificada = $notificada;
            $dispersion->fecha_dispersion =  $fecha_dipsersion;
            $dispersion->save();
            return $dispersion;
        }

        public function updateExibicion($idExibicion, $estatus, $notificada, $fecha_dipsersion){

            $dispersion = ExhibicionesRecargas::find($idExibicion);
            if($dispersion){
                $dispersion->estatus = $estatus;
                $dispersion->notificada = $notificada;
                $dispersion->fecha_dispersion =  $fecha_dipsersion;
                $dispersion->save();
            }
            return $dispersion;
        }
}
