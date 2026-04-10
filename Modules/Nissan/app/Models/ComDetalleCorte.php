<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComDetalleCorte extends Model
{
    use HasFactory;

    protected $table = 'com_detalle_corte';
    
    protected $connection = 'autos';

    // protected $primaryKey = 'id';

    // public $incrementing = true;

    // protected $keyType = 'int';

    // public $timestamps = false;

    protected $fillable = [
        'com_vendedores_corte_id',
        'origen_tipo',
        'fecha',
        'origen_id',
        'monto_comision',
        'monto_venta',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id' => 'integer',
        'com_vendedores_corte_id' => 'integer',
        'origen_id' => 'integer',

        'monto_comision' => 'float',
        'monto_venta' => 'float',
    ];

    public function vendedorCorte()
    {
        return $this->belongsTo(ComVendedoresCorte::class, 'com_vendedores_corte_id');
    }

    public function scopePorVendedorCorte($query, $vendedorCorteId)
    {
        return $query->where('com_vendedores_corte_id', $vendedorCorteId);
    }

    public function scopePorOrigen($query, $tipo)
    {
        return $query->where('origen_tipo', $tipo);
    }

    public function esVenta()
    {
        return $this->monto_venta > 0;
    }

    public function esComision()
    {
        return $this->monto_comision > 0;
    }
}