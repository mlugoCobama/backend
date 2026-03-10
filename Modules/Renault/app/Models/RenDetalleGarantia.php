<?php

namespace Modules\Renault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Renault\Database\Factories\RenDetalleGarantiaFactory;

class RenDetalleGarantia extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    use HasFactory;
    

    protected $table = 'ren_detalle_garantia';

    protected $fillable = [
        'descripcion',
        'tiempo',
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
    



    protected static function newFactory(): RenDetalleGarantiaFactory
    {
        //return RenDetalleGarantiaFactory::new();
    }
}
