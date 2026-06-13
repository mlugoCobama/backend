<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\TarjetasTokaFactory;

class TarjetasToka extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tarjeta',
        'proxy_number',
        'cuenta',
        'nomina',
        'com_empresas_toka_id',
        'activo',
        'estatus'
    ];

    protected $table = 'com_tarjetas_toka';

    protected static function newFactory(): TarjetasTokaFactory
    {
        //return TarjetasTokaFactory::new();
    }

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }

    public function cliente(){
        return $this->belongsTo(EmpresasToka::class, 'com_empresas_toka_id', 'id');
    }

    public function asignaciones(){
        return $this->hasMany(VehiculosToka::class, 'com_id_tarjetas_toka', 'id');
    }

    public function asignacionActiva(){
        return $this->hasMany(VehiculosToka::class, 'com_id_tarjetas_toka', 'id')->whereNull('fecha_fin');
    }
}
