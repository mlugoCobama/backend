<?php

namespace Modules\ParqueVehicular\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Models\Proveedores;
use Modules\ParqueVehicular\Database\Factories\VehiculoProveedorFactory;

class VehiculoProveedor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'com_datos_vehiculos_id',
        'com_proveedores_id',
        'observaciones',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'estatus',
        'intercompania',
        'descripcion_falla',
        'folio',
        'tipo'
    ];

    protected $table = 'pv_ordenes_proveedores';

    protected static function newFactory(): VehiculoProveedorFactory
    {
        //return VehiculoProveedorFactory::new();
    }

    /**
     * Scopes
     */

    public function scopeActive($query){
        return $query->where('activo', 1);
    }


    /**
     * Relaciones
     */

    public function vehiculo(){
        return $this->belongsTo(DatosVehiculo::class, 'com_datos_vehiculos_id', 'id');
    }

    public function proveedor(){
        return $this->belongsTo(Proveedores::class, 'com_proveedores_id', 'id');
    }


}
