<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisosAutorizarVehiculos extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ejecutar primero este
        DB::connection('dashboard')->table('permissions')->insert([
               ['name' => 'view bnt autorizar vehiculo', 'descripcion' => 'Ver el boton de autorizar vehiculo', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
        ]);

        //Permission id debe de cambiar al permiso generado
        DB::connection('dashboard')->table('model_has_permissions')->insert([
                ['model_id' => '2395', 'model_type' => 'App\Models\User', 'permission_id' => '209'],
                ['model_id' => '2247', 'model_type' => 'App\Models\User', 'permission_id' => '209'],
        ]);
    }
}
