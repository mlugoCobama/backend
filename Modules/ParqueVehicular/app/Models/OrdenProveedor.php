<?php

namespace Modules\ParqueVehicular\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Models\Proveedores;
use Modules\ParqueVehicular\Database\Factories\OrdenProveedorFactory;

class OrdenProveedor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'activo',
        'com_proveedores_id',
        'pv_orden_servicio_id',
        'fecha_asignacion',
        'autorizada',
        'diagnostico'
    ];

    protected $table = 'pv_ordenes_proveedores';

    protected static function newFactory(): OrdenProveedorFactory
    {
        //return OrdenProveedorFactory::new();
    }

    public function proveedor(){
        return $this->belongsTo(Proveedores::class, 'com_proveedores_id', 'id');
    }

    public function ordenServicio(){
        return $this->belongsTo(OrdenServicio::class, 'pv_orden_servicio_id', 'id');
    }

    public function eventos(){
        return $this->hasMany(EventosProvOrden::class,'pv_ordenes_proveedores_id', 'id' );
    }

    public function trabajos(){
        return $this->hasMany(Trabajos::class,'pv_orden_proveedor_id', 'id' );
    }

    public function evidencias(){
        return $this->hasMany(MultimediaProvOrden::class, 'pv_orden_proveedor_id', 'id');
    }
}
