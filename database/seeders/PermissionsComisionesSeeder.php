<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsComisionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('permissions')->insert([
            [
                'name' => 'view comisiones cxc access',
                'descripcion' => 'Vista de libro ventas datos para cxc',
                'ucoip_modulo_id' => 5,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
                'sistema' => 3,
            ],
            [
                'name' => 'view comisiones gv access',
                'descripcion' => 'Vista de libro ventas datos para GV',
                'ucoip_modulo_id' => 5,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
                'sistema' => 3,
            ],
            [

                'name' => 'view comisiones conta access',
                'descripcion' => 'Vista de libro ventas datos para conta',
                'ucoip_modulo_id' => 5,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
                'sistema' => 3,
            ],
            [

                'name' => 'view comisiones rh access',
                'descripcion' => 'Vista de libro ventas datos para RRHH',
                'ucoip_modulo_id' => 5,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
                'sistema' => 3,
            ],
            [
                'name' => 'view comisiones pagados access',
                'descripcion' => 'Vista de libro ventas datos para pagados',
                'ucoip_modulo_id' => 5,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
                'sistema' => 3,
            ],
            [
                'name' => 'view comisiones all access',
                'descripcion' => 'Vista de libro ventas datos para all acces (desarollo)',
                'ucoip_modulo_id' => 5,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
                'sistema' => 3,
            ],
            [

                'name' => 'view submodulo comisiones',
                'descripcion' => 'ver submodulo comisiones',
                'ucoip_modulo_id' => 5,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
                'sistema' => 3,
            ],
            [

                'name' => 'view submodulo vendedores',
                'descripcion' => 'ver submodulo vendedores',
                'ucoip_modulo_id' => 5,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
                'sistema' => 3,
            ],
            [

                'name' => 'view submoduloe tabuladores',
                'descripcion' => 'ver submodulo tabuladores',
                'ucoip_modulo_id' => 5,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
                'sistema' => 3,
            ],
        ]);
    }
}
