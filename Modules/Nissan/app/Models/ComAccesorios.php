<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\ComAccesoriosFactory;

class ComAccesorios extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'com_vendedores_id',
        'no_factura', 
        'sub_total_factura', 
        'porcentaje_apv', 
        'comision_apv_pesos', 
        'activo', 
        'estatus', 
        'created_at', 
        'updated_at', 
        'fecha_factura', 
        'agencia',
        'comentario',
        'observaciones'
    ];

    protected $table = 'com_accesorios';

    protected $connection = 'autos';

    protected static function newFactory(): ComAccesoriosFactory
    {
        //return ComAccesoriosFactory::new();
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'com_vendedores_id', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(ComDetalleAccesorio::class, 'com_accesorios_id', 'id');
    }

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
}
