<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisosMenuMacro extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    //    DB::connection('dashboard')->table('permissions')->insert([
                // ['name' => 'view parque vehicular', 'descripcion' => 'Ver menu parque vehicular', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
                // ['name' => 'view modulo macro taller', 'descripcion' => 'Ver modulo macro taller', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
                // ['name' => 'view macro tecnicos', 'descripcion' => 'Ver menu tecnicos', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
                // ['name' => 'view macro almacen', 'descripcion' => 'Ver menu almacen', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
        // ]);

        DB::connection('dashboard')->table('model_has_permissions')->insert([
            // ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 11],
            ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 218],
            ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 219],
            ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 220],
        ]);
    }
}
