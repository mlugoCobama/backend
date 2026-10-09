<?php

namespace Modules\ParqueVehicular\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ParqueVehicular\Database\Factories\TrabajosFactory;

class Trabajos extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'descripcion',
        'pv_orden_proveedor_id',
        'precio'
    ];

    protected $table = "pv_trabajos_orden";

    protected static function newFactory(): TrabajosFactory
    {
        //return TrabajosFactory::new();
    }

    public function partesTrabajo(){
        return $this->hasMany(PartesTrabajo::class, 'pv_trabajos_orden_id', 'id');
    }
}
