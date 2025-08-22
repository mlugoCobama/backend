<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\DetalleAutotanqueFactory;

class DetalleAutotanque extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'com_detalle_solicitud_id',
        'com_datos_vehiculos_id',
    ];
    

    /**
     * Nombre de la tabla
     */
    protected $table = 'com_detalle_autotanque';

    public function DetalleSolicitud()
    {
        return $this->belongsTo(DetalleSolicitud::class, 'com_detalle_solicitud_id', 'id');
    }

    public function DatosVehiculo()
    {
        return $this->belongsTo(DatosVehiculo::class, 'com_datos_vehiculos_id', 'id');
    }



    protected static function newFactory(): DetalleAutotanqueFactory
    {
        //return DetalleAutotanqueFactory::new();
    }
}
