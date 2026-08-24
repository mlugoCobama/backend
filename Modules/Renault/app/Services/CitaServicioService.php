<?php

namespace Modules\Renault\Services;

use Illuminate\Support\Facades\DB;
use Modules\Renault\Models\RenCitasServicio;
use Modules\Renault\Models\RenEventosCita;

class CitaServicioService
{
    public function generarEvento($idCita, $tipoEvento){
        $evento =  new RenEventosCita();
       $evento->ren_cat_eventos_id = $tipoEvento;
       $evento->ren_citas_servicio_id = $idCita;
       $evento->inicio_evento = now();
       $evento->save();

       return $evento;
    }

    public function finalizarEvento($idEvento, $observaciones = null){
        $evento =  RenEventosCita::find($idEvento);
       $evento->fin_evento = now();
       $evento->observaciones = $observaciones;
       $evento->save();

       return $evento;
    }

    public function updateEstatus($idCita, $estatus){
        $estatusTexto = match ($estatus) {
            1 => 'AC', 2 => 'AT', 3 => 'AL',
            4 => 'CA', 5 => 'TE', 6 => 'EN',
            7 => 'FN',
            default => $estatus
        };

        RenCitasServicio::where('id', $idCita)->update([ 'estatus' => $estatusTexto]);
    }

    public function obtenerOProcesarCitas(){
        $data = [];
        $date = date('Ymd');
        $empleadosCache = [];
        for ($j=1; $j < 5; $j++) {
            $citas = DB::connection('renault')
                    ->table('Se_Citas')
                    ->select(
                        'Se_Citas.citas_folio',
                        'Se_Citas.citas_empl_clave',
                        'empleados.empl_nombre',
                        'Se_Citas.citas_fechacita',
                        'Se_Citas.citas_nombre',
                        'Se_Citas.citas_apaterno',
                        'Se_Citas.citas_amaterno',
                        'Se_Citas.citas_modelo',
                        'Se_Citas.citas_tipo',
                        'Se_Citas.citas_placas',
                        'Se_Citas.citas_observaciones',
                        'Se_Citas.citas_Color1',
                        'Se_Citas.citas_AnioModelo',
                        'Se_Citas.citas_status',
                        'Se_Citas.citas_TipoCita',
                        'Se_Citas.citas_NoSerie',
                        'Se_Citas.citas_TelefonoContacto',
                        'Se_Citas.citas_Domicilio',
                        'Se_Citas.citas_Kilometraje',
                        'Se_Citas.citas_email',
                        'Se_Citas.citas_RFC',
                        )
                    ->join('empleados', 'Se_Citas.citas_empl_clave', '=', 'empleados.empl_clave')
                    ->where('Se_Citas.citas_idagencia', '=',$j)
                    ->where('Se_Citas.citas_status', '<>', 'BO')
                    ->whereBetween('Se_Citas.citas_fechacita', [$date.' 00:00:00.000',$date.' 23:59:59.997'])
                    ->orderBy('Se_Citas.citas_fechacita', 'asc')
                    ->orderBy('Se_Citas.citas_empl_clave', 'asc')
                    ->get();

                    foreach ($citas as $cita) {
                        $empleadoClave = $cita->citas_empl_clave;
                        $empleadoNombre = $cita->empl_nombre;

                        // Si no está en cache, lo consultamos y lo guardamos
                        if (!isset($empleadosCache[$empleadoClave])) {
                            $empleadoId = $this->getAps($empleadoNombre, $j);
                            $empleadosCache[$empleadoClave] = $empleadoId ??  null;
                        }

                        // Ahora ya puedes usar $empleadosCache[$empleadoClave] sin repetir consulta
                        $cita->empleado_id_intranet = $empleadosCache[$empleadoClave];
                    }



         for ($i = 0; $i < count($citas); $i++) {

             $existe = RenCitasServicio::where('folio', $citas[$i]->citas_folio)->get();

             if ($existe->count() == 0) {
                 $citaCita = new RenCitasServicio();
                 $citaCita->folio = $citas[$i]->citas_folio;
                 $citaCita->empleado_id = $citas[$i]->citas_empl_clave;
                 $citaCita->fecha = $citas[$i]->citas_fechacita;
                 $citaCita->nombre = $citas[$i]->citas_nombre;
                 $citaCita->apellido_paterno = $citas[$i]->citas_apaterno;
                 $citaCita->apellido_materno = $citas[$i]->citas_amaterno;
                 $citaCita->rfc = $citas[$i]->citas_RFC;
                 $citaCita->telefono = $citas[$i]->citas_TelefonoContacto;
                 $citaCita->domicilio = $citas[$i]->citas_Domicilio;
                 $citaCita->email = $citas[$i]->citas_email;
                 $citaCita->vin = $citas[$i]->citas_NoSerie;
                 $citaCita->modelo = $citas[$i]->citas_modelo;
                 $citaCita->placas = $citas[$i]->citas_placas;
                 $citaCita->color = $citas[$i]->citas_Color1;
                 $citaCita->tipo = $citas[$i]->citas_tipo;
                 $citaCita->anio = $citas[$i]->citas_AnioModelo;
                 $citaCita->kilometraje = $citas[$i]->citas_Kilometraje;
                 $citaCita->observaciones = $citas[$i]->citas_observaciones;
                 $citaCita->tipo_cita = $citas[$i]->citas_TipoCita;
                 $citaCita->estatus = $citas[$i]->citas_status;
                 $citaCita->agencia_id = $j;
                 $citaCita->id_intranet = $citas[$i]->empleado_id_intranet;
                 $citaCita->save();
             }
         }

            // $data[$j] = $citas;
        }
    }

        private function getAps($empleadoNombre, $idAgencia){
        $intercompania = match($idAgencia){
                                1 => '7064', 2 => '7062', 3 => '7063',  4 => '7061', default => $idAgencia,
                        };
       return DB::connection('intranet')
                                ->table('glpi_users')
                                ->where('firstname', $empleadoNombre)
                                ->where('name','like', '%aps%')
                                ->where('intercompania', $intercompania)
                                ->value('id');
    }
}
