<?php

namespace App\Queries\SqlServer\Comisiones;

use Illuminate\Support\Facades\DB;

class TomaUnidadesQuery
{
    public function getTomaUnidades($connection, $anio, $clave)
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
                'M.mode_descripcion',
                'IA.vehi_idagencia AS id_agencia'
            )
            ->get();
    }
}
