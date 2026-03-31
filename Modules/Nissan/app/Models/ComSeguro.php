<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\ComSeguroFactory;

class ComSeguro extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'com_vendedores_id', 
        'folio',
        'poliza', 
        'fecha_emision', 
        'prima_neta', 
        'comision_apv_pesos', 
        'observaciones', 
        'comentario', 
        'activo', 
        'estatus'
    ];

    protected $table = 'com_seguro';

    protected $connection = 'autos';

    protected static function newFactory(): ComSeguroFactory
    {
        //return ComSeguroFactory::new();
    }

         // Relación
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'com_vendedores_id', 'id');
    }

    public function venta()
    {
        return $this->belongsTo(DatosVenta::class, 'com_datos_venta_id', 'id');
    }

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
}
