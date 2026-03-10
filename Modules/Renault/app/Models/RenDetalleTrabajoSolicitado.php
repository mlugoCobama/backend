<?php

namespace Modules\Renault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Renault\Database\Factories\RenDetalleTrabajoSolicitadoFactory;

class RenDetalleTrabajoSolicitado extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'ren_detalle_trabajo_solicitado';

        protected $fillable = [
            'descripcion',
            'partes',
            'ren_entrada_vehiculo_id',
        ];

        protected $connection = 'autos';
        /**
         * Relación con la tabla ren_entrada_vehiculo
         */
        public function entradaVehiculo()
        {
            return $this->belongsTo(RenEntradaVehiculo::class, 'ren_entrada_vehiculo_id');
        }

    protected static function newFactory(): RenDetalleTrabajoSolicitadoFactory
    {
        //return RenDetalleTrabajoSolicitadoFactory::new();
    }
}
