<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoSlectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::connection('dashboard')->table('permissions')->insert([
                ['name' => 'view select multiempresa', 'descripcion' => 'Ver el select para intercambiar de empresas', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
        ]);

        DB::connection('dashboard')->table('model_has_permissions')->insert([
                ['model_id' => 'de del usauio al que se le asigna le permiso', 'model_type' => 'App\Models\User', 'permission_id' => 'Aquí va ir el i del permiso cuando lo registre'],
        ]);
    }
}
