<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisosBtnsComprasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 02/10/2025
     * Compras / Compras Macro
     * Permisos para los botones con acciones en compras
     */
    public function run(): void
    {
       DB::connection('dashboard')->table('permissions')->insert([
                ['name' => 'view btn save precios', 'descripcion' => 'Ver botón guardar precios compra', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
                ['name' => 'view btn cancelar', 'descripcion' => 'Ver boton cancelar', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
                ['name' => 'view btn upload comprobante pago', 'descripcion' => 'Ver botón subir comprobante de pago', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
                ['name' => 'view btn upload complemento pago', 'descripcion' => 'Ver botón subir comprobante de pago', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
                ['name' => 'view btn mark pagado', 'descripcion' => 'Ver botón guardar precios compra', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
                ['name' => 'view panel auto orden compra', 'descripcion' => 'Ver panel autorizar oc', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
                ['name' => 'view btn edit detalles', 'descripcion' => 'Ver botón editar detalles sc', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1], 
                ['name' => 'view btn generar orden', 'descripcion' => 'Ver botón generar orden de compra', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
                ['name' => 'view btn cotizar', 'descripcion' => 'Ver botón cotizar', 'ucoip_modulo_id' => 5, 'guard_name' => 'web', 'sistema' => 1],
            ]);
    }
}
