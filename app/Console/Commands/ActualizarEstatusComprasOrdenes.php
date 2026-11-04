<?php
namespace App\Console\Commands;

use App\Enums\EstatusOrdenCompra;
use App\Enums\EstatusSolicitud;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ActualizarEstatusComprasOrdenes extends Command
{
    protected $signature = 'compras:actualizar-estatus';
    protected $description = 'Actualiza el estatus de solicitudes y órdenes de compra según su modo de pago.';

    public function handle()
    {
        $this->info('Iniciando actualización de estatus...');

        // Obtenemos los registros utilizando tu consulta original con los Joins necesarios
        $registros = DB::table('com_solicitudes_compra as csc')
            ->join('com_cotizaciones as cc', 'csc.id', '=', 'cc.solicitudes_compra_id')
            ->join('com_orden_compra as coc', 'coc.cotizaciones_id', '=', 'cc.id')
            ->where('csc.estatus', 10)
            ->where('csc.activo', 1)
            ->select(
                'csc.id as id_solicitud',
                'coc.id as id_orden',
                'coc.modo_pago'
            )
            ->get();

        if ($registros->isEmpty()) {
            $this->info('No se encontraron registros para actualizar.');
            return;
        }

        $this->info("Se encontraron {$registros->count()} registros para procesar.");

        // Usamos una transacción para asegurar que todo se actualice correctamente
        DB::transaction(function () use ($registros) {
            foreach ($registros as $registro) {

                if ($registro->modo_pago == 1) {
                    $nuevoEstatusSolicitud = EstatusSolicitud::SOLICITADO_PAGO;
                    $nuevoEstatusOrden = EstatusOrdenCompra::SOLICITADO_PAGO;
                } elseif ($registro->modo_pago == 2) {
                    $nuevoEstatusSolicitud = EstatusSolicitud::EN_SURTIDO;
                    $nuevoEstatusOrden = EstatusOrdenCompra::EN_SURTIDO;
                } else {
                    continue;
                }

                // Actualizar solicitud de compra
                DB::table('com_solicitudes_compra')
                    ->where('id', $registro->id_solicitud)
                    ->update(['estatus' => $nuevoEstatusSolicitud]);

                // Actualizar orden de compra
                DB::table('com_orden_compra')
                    ->where('id', $registro->id_orden)
                    ->update(['estatus' => $nuevoEstatusOrden]);
            }
        });

        $this->info('¡Estatus actualizados correctamente!');


        $this->info('Borrando permisos de estados de solicitud...');

        // Ejecutamos la consulta de borrado
        $deletedRows = DB::table('model_has_permissions')
            ->whereIn('permission_id', [249, 250])
            ->delete();

        $this->info("¡Listo! Se han eliminado {$deletedRows} permisos correctamente.");


        $this->info('Asignando permisos de carga de facturas...');

        // Ejecutamos la consulta de borrado
        $userIds = [2567,2307,419,202,172,153,147,119,112,100,69,56,38];
        // $permissionIds = [202, 491];
        $permissionIds = [492,493];
        $modelType = 'App\Models\User';

        $dataToInsert = [];

        foreach ($userIds as $userId) {
            foreach ($permissionIds as $permissionId) {
                $dataToInsert[] = [
                    'permission_id' => $permissionId,
                    'model_type'    => $modelType,
                    'model_id'      => $userId,
                ];
            }
        }

        DB::table('model_has_permissions')->insertOrIgnore($dataToInsert);

        $this->info("¡Listo! Se han agregado los nuuevos permisos {$deletedRows} permisos correctamente.");



    }
}
