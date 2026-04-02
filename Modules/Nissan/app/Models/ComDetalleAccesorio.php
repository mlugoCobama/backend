<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\ComDetalleAccesorioFactory;

class ComDetalleAccesorio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'concepto',
        'importe',
        'cantidad',
        'com_accesorios_id',
        'activo'
    ];

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'com_vendedores_id', 'id');
    }

    protected $table = 'com_detalles_accesorios';

    protected $connection = 'autos';

    protected static function newFactory(): ComDetalleAccesorioFactory
    {
        //return ComDetalleAccesorioFactory::new();
    }

    public function comisionAccesorio()
    {
        return $this->belongsTo(ComAccesorios::class, 'com_accesorios_id', 'id');
    }
}
