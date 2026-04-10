<?php

namespace Modules\Nissan\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComCorte extends Model
{
    use HasFactory;

    protected $table = 'com_corte';

    protected $connection = 'autos';

    // protected $primaryKey = 'id';

    // public $incrementing = true;

    // protected $keyType = 'int';

    // public $timestamps = false;

    protected $fillable = [
        'fecha_corte',
        'fecha_inicio',
        'fecha_fin',
        'clave_corte',
        'estatus',
        'created_at',
        'updated_at',
        'agencia'
    ];

    protected $casts = [
        'id' => 'integer',
        'fecha_corte' => 'datetime',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estatus' => 'boolean',
        'agencia' => 'integer'
    ];


    // protected $attributes = [
    //     'estatus' => 1
    // ];

    public function scopeActivos($query)
    {
        return $query->where('estatus', 1);
    }

    public function scopePorAgencia($query, $agenciaId)
    {
        return $query->where('agencia', $agenciaId);
    }

    public function setFechaCorteAttribute($value)
    {
        $this->attributes['fecha_corte'] = $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
    }

    public function vendedoresCorte()
    {
        return $this->hasMany(ComVendedoresCorte::class, 'com_corte_id');
    }
}
