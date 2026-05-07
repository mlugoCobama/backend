<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Models\Proveedores;

// use Modules\Ucoip\Database\Factories\ServicioFactory;

class Servicio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'intercompania',
        'proveedor_id',
        'tipo_servicio_id',
        'nombre',
        'descripcion',
        'identificador_externo',
        'costo_base',
        'moneda',
        'periodicidad',
        'fecha_inicio',
        'fecha_fin',
        'dia_pago',
        'dia_corte',
        'renovable',
        'activo',
    ];

    protected $table = 'ucoip_servicios';

    public function proveedor(){
        return $this->belongsTo(Proveedores::class,'proveedor_id', 'id');
    }

    public function tipoServicio(){
        return $this->belongsTo(CatServicio::class,'tipo_servicio_id', 'id');
    }

    // protected static function newFactory(): ServicioFactory
    // {
    //     // return ServicioFactory::new();
    // }
}
