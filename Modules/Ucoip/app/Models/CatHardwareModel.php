<?php

namespace Modules\Ucoip\Models;

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
        'icono'
    ];

    public $timestamps = false;
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
}
