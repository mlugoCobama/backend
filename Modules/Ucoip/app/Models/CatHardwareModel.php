<?php

namespace Modules\Ucoip\Models;

use App\Enums\EstatusActivos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\CatHardwareModelFactory;

class CatHardwareModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tipo',
        'icono',
        'categoria',
        'campos',
        'mantenible'
    ];

    public $timestamps = false;

    protected $casts = [
        'campos' => 'array',
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'ucoip_cat_hardware';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Un hardware es de un tipo
     */
    public function Hardware() {
        $this->belongsTo(HardwarePcModel::class, 'cat_hardware_id', 'id');
    }

    public function hardwareDisponible()
    {
        return $this->hasMany(HardwarePcModel::class, 'cat_hardware_id', 'id')
                    ->where('estado', EstatusActivos::DISPONIBLE)
                    ->select('id', 'marca', 'modelo', 'no_serie', 'cat_hardware_id');
    }

    public function scopeUsuarios ($query) {
        return $query->where('categoria', 1);
    }

    public function scopeInfraestructura ($query) {
        return $query->where('categoria', 2);
    }

}
