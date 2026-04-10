<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComVendedoresCorte extends Model
{
    use HasFactory;

    protected $table = 'com_vendedores_corte';

    protected $connection = 'autos';

    // protected $primaryKey = 'id';

    // public $incrementing = true;

    // protected $keyType = 'int';

    // public $timestamps = false;

    protected $fillable = [
        'com_corte_id',
        'com_vendedores_id',
        'total_comisiones',
        'total_nuevos',
        'total_seminuevos',
        'total_seguros',
        'total_financiamiento',
        'total_accesorios',
        'total_toma_unidad',
        'total_otros',
        'por_desc_fact',
        'com_factura',
        'desc_nomina',
        'desc_pretaciones',
        'des_otros',
        'desc_c_casa',
        'desc_infonavit',
        'monto_dispersar',
        'nomina',
        'observaciones',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id' => 'integer',
        'com_corte_id' => 'integer',
        'com_vendedores_id' => 'integer',

        'total_comisiones' => 'float',
        'total_nuevos' => 'float',
        'total_seminuevos' => 'float',
        'total_seguros' => 'float',
        'total_financiamiento' => 'float',
        'total_accesorios' => 'float',
        'total_toma_unidad' => 'float',
        'total_otros' => 'float',

        'por_desc_fact' => 'float',
        'com_factura' => 'float',

        'desc_nomina' => 'float',
        'desc_pretaciones' => 'float',
        'des_otros' => 'float',
        'desc_c_casa' => 'float',
        'desc_infonavit' => 'float',

        'monto_dispersar' => 'float',
        'nomina' => 'float',
    ];

    public function corte()
    {
        return $this->belongsTo(ComCorte::class, 'com_corte_id');
    }

    public function detalles()
    {
        return $this->hasMany(ComDetalleCorte::class, 'com_vendedores_corte_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'com_vendedores_id');
    }

    public function scopePorCorte($query, $corteId)
    {
        return $query->where('com_corte_id', $corteId);
    }

    public function scopePorVendedor($query, $vendedorId)
    {
        return $query->where('com_vendedores_id', $vendedorId);
    }

    public function getTotalDescuentosAttribute()
    {
        return 
            $this->desc_nomina +
            $this->desc_pretaciones +
            $this->des_otros +
            $this->desc_c_casa +
            $this->desc_infonavit;
    }
}