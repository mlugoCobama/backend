<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportarTomaUnidades extends Command
{
    protected $signature = 'toma:importar {anio} {clave}';
    protected $description = 'Importa unidades usadas al módulo de toma de unidad parametros Anio y clave (US ó NU)' ;

    public function handle()
    {
        $anio = $this->argument('anio');
        $clave = strtoupper($this->argument('clave'));

        $conexiones = [
            'nissan_universidad' => 710,
        ];

        foreach ($conexiones as $conexion => $agenciaId) {

            $this->info("Procesando inventario: {$conexion}");

            $this->procesarConexion($conexion, $agenciaId, $anio, $clave);
        }

        $this->info('Importación de tomas completada');
    }

    private function procesarConexion($conexion, $agenciaId, $anio, $clave)
    {
        $origen  = DB::connection($conexion);
        $destino = DB::connection('autos');

        $unidades = $this->queryUnidades($origen, $anio, $clave);

        if ($unidades->isEmpty()) return;

        DB::connection('autos')->transaction(function () use ($unidades, $destino, $agenciaId, $anio, $clave) {

            foreach ($unidades as $unidad) {

                // Validar duplicado por número de inventario
                $existe = $destino->table('com_toma_unidad')
                    ->where('no_serie', $unidad->vehi_serie)
                    ->where('agencia', $agenciaId)
                    ->where('no_inventario', ($clave.'-'.$anio.'-'.$unidad->vehi_numeroinventario))
                    ->exists();

                if ($existe) continue;

                // Insertar registro
                $destino->table('com_toma_unidad')->insert([
                    // 'com_vendedores_id'   => null, // si después lo quieres relacionar
                    'no_inventario'       => $clave.'-'.$anio.'-'.$unidad->vehi_numeroinventario,
                    'vehiculo'            => $unidad->mode_descripcion . ' ' . $unidad->vehi_anio,
                    'comision_apv_pesos'  => 2000,
                    'fecha_toma'          => $unidad->vehi_fechaasignacion,
                    'id_com_datos_venta'  => null,
                    'observaciones'       => null,
                    'comentario'          => null,
                    'estatus'             => 1,
                    'activo'              => 1,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                    'no_serie'            => $unidad->vehi_serie,
                    'agencia'             => $agenciaId,
                    'tipo_apv'            => '',
                    'com_corte_id'        => null,
                ]);
            }
        });
    }

    private function queryUnidades($connection, $anio, $clave)
    {
        return $connection->table('Vt_InventarioAutos as IA')
            ->join('Vt_Modelos as M', 'M.mode_modeloid', '=', 'IA.vehi_mode_modeloid')
            ->where('IA.vehi_clas_clave', $clave)
            ->where('IA.vehi_anio', $anio)
            ->select(
                'IA.vehi_numeroinventario',
                'IA.vehi_anio',
                'IA.vehi_fechaasignacion',
                'IA.vehi_serie',
                'M.mode_descripcion'
            )
            ->get();
    }
}