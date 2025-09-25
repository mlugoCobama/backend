<?php

namespace Modules\Compras\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\OrdenTrabajoFactory;

class OrdenTrabajo extends Model
{
    use HasFactory;
    use Auditable;
        /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'orden_trabajo',
        'formato_orden',
        'com_datos_vehiculo_id',
        'com_solicitudes_compra_id',
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'com_ordenes_trabajo';
    /**
     * The attributes that are mass assignable.
     */

    protected static function newFactory(): OrdenTrabajoFactory
    {
        //return OrdenTrabajoFactory::new();
    }

    public function SolicitudCompra(){
        return $this->belongsTo(SolicitudesCompra::class, 'com_solicitudes_compra_id', 'id');
    }
}
