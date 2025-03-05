<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentasPostVenta extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $fillable = [
        'ventas_servicio',
        'total_ventas_ref',
        'refacciones_servicio',
        'refacciones_hyp',
        'refacciones_mostrador',
        'fecha',
        'sucursales_id'
    ];

    /**
     * Nombre de la tabla
     */
    // protected $connection = 'dashboard';
    protected $connection = 'dashboard1';
    protected $table = 'ventas_post_venta';

         /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     *
     */
    public function Sucursal() {
        $this->belongsTo(Sucursales::class);
    }
}
