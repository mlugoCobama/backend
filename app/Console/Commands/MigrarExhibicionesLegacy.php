<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Models\ComRecargasVehiculos;
use Modules\Compras\Models\ExhibicionesRecargas;
use Modules\Compras\Models\SolcitudDiesel;

class MigrarExhibicionesLegacy extends Command
{
    protected $signature = 'diesel:migrar-exhibiciones {--dry-run : Solo mostrar qué se migraría, sin escribir}';
    protected $description = 'Migra registros viejos de com_recargas_vehiculos a com_exhibiciones_recargas (numero_exhibicion = 1)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // Solo recargas que AÚN NO tienen ninguna exhibición registrada
        $recargasSinMigrar = ComRecargasVehiculos::whereDoesntHave('exhibiciones')
            ->whereNotNull('com_solicitud_diesel_id')
            ->get();

        $this->info("Encontradas {$recargasSinMigrar->count()} recargas sin exhibiciones.");

        $migradas = 0;
        $omitidas = 0;

        DB::beginTransaction();
        try {
            foreach ($recargasSinMigrar as $recarga) {
                $recarga->monto_autorizado = $recarga->monto_solicitado;
                $recarga->save();

                $solicitud = SolcitudDiesel::find($recarga->com_solicitud_diesel_id);

                if (!$solicitud) {
                    $this->warn("Recarga {$recarga->id} sin solicitud asociada, se omite.");
                    $omitidas++;
                    continue;
                }

                // Estatus 1 (pendiente): no hay nada capturado aún, no se migra
                if ($solicitud->estatus == 1) {
                    $omitidas++;
                    continue;
                }

                [$guardada, $notificada, $dispersada] = match ((int) $solicitud->estatus) {
                    2 => [1, 0, 0],
                    3 => [1, 1, 1],
                    4 => [1, 1, 0],
                    default => [0, 0, 0],
                };

                $this->line(sprintf(
                    "Recarga %d (solicitud %d, folio %s): estatus=%d -> guardada=%d notificada=%d dispersada=%d, saldo_actual=%s monto_dispersado=%s",
                    $recarga->id,
                    $solicitud->id,
                    $solicitud->folio,
                    $solicitud->estatus,
                    $guardada, $notificada, $dispersada,
                    $recarga->saldo_actual ?? 'NULL',
                    $recarga->monto_dispersado ?? 'NULL'
                ));

                if (!$dryRun) {
                    ExhibicionesRecargas::create([
                        'numero_exhibicion'         => 1,
                        'saldo_actual_previo'       => $recarga->saldo_actual ?? 0,
                        'monto_dispersado'          => $recarga->monto_dispersado ?? 0,
                        'estatus'                   => $recarga->estatus ?? $solicitud->estatus,
                        'fecha_dispersion'          => $recarga->fecha_dispersion,
                        'com_recargas_vehiculos_id' => $recarga->id,
                        'guardada'                  => $guardada,
                        'notificada'                => $notificada,
                        'dispersada'                => $dispersada,
                    ]);
                }

                $migradas++;
            }

            if ($dryRun) {
                DB::rollBack();
                $this->info("DRY RUN: {$migradas} se migrarían, {$omitidas} se omitirían. No se escribió nada.");
            } else {
                DB::commit();
                $this->info("Migración completa: {$migradas} exhibiciones creadas, {$omitidas} omitidas.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error, se revirtió todo: {$e->getMessage()}");
        }
    }
}
