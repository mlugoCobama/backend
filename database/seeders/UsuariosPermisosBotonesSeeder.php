<?php

namespace Database\Seeders;

use Carbon\PHPStan\Macro;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsuariosPermisosBotonesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 02/10/2025
     * Compras / Compras Macro
     * Asignación de permisos de botones de compras y compras macro
     */
    public function run(): void
    {
        //! DB::connection('dashboard')->statement('SET FOREIGN_KEY_CHECKS = 0;');
        DB::connection('dashboard')->table('model_has_permissions')->insert([
            //Ver boton gaurdar precios compra
                ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 200],
            //Ver btn cancelar 
                ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 201],
            //Ver botón subir comprobante de pago
                // ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 202],
            //Ver botón subir complemento de pago
                // ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 203],
            //Ver boton gaurdar precios compra
                ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 204],
            //Ver panel autorizar oc
                ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 205],
            //Ver boton editar detalles sc
                ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 206],
            //Ver boton generear orden de compra
                ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 207],
            // Ver boton cotizar
                ['model_id' => 2395, 'model_type' => 'App\Models\User', 'permission_id' => 208],
            
            /**
             * Compras puede 
             * editar detalles
             * GUardar precios
             * Cotizar
             * Generar orden de compra
             * Cancelar solicitudes
             * Aceptar/Rechazar ordenes de compra (Encaragado compras)
             * Subir complemento de pago
             */
            //Compras
            ['model_id' => 2039, 'model_type' => 'App\Models\User', 'permission_id' => 206],
            ['model_id' => 2039, 'model_type' => 'App\Models\User', 'permission_id' => 200],
            ['model_id' => 2039, 'model_type' => 'App\Models\User', 'permission_id' => 207],
            ['model_id' => 2039, 'model_type' => 'App\Models\User', 'permission_id' => 208],
            ['model_id' => 2039, 'model_type' => 'App\Models\User', 'permission_id' => 201],
            ['model_id' => 2039, 'model_type' => 'App\Models\User', 'permission_id' => 205],

            //Aux Compras
            ['model_id' => 2364, 'model_type' => 'App\Models\User', 'permission_id' => 206],
            ['model_id' => 2364, 'model_type' => 'App\Models\User', 'permission_id' => 200],
            ['model_id' => 2364, 'model_type' => 'App\Models\User', 'permission_id' => 207],
            ['model_id' => 2364, 'model_type' => 'App\Models\User', 'permission_id' => 208],
            ['model_id' => 2364, 'model_type' => 'App\Models\User', 'permission_id' => 201],

            // Macro
            ['model_id' => 371, 'model_type' => 'App\Models\User', 'permission_id' => 201],
            
            ]);
        //! DB::connection('dashboard')->statement('SET FOREIGN_KEY_CHECKS = 1;');
    }
}
