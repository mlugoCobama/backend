<?php

namespace Modules\Renault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Renault\Database\Factories\RenEncuestaCitaFactory;

class RenEncuestaCita extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fecha',
        'ren_citas_servicio_id',
        'ruta_firma'
    ];

    protected $table = 'ren_encuesta_cita';
    protected $connection = 'autos';

    protected static function newFactory(): RenEncuestaCitaFactory
    {
        //return RenEncuestaCitaFactory::new();
    }

    public function repuestas(){
        return $this->hasMany(RenRespuestasEncuesta::class, 'ren_encuesta_cita_id', 'id');
    }

    public function cita(){
        return $this->belongsTo(RenCitasServicio::class, 'ren_citas_servicio_id', 'id');
    }
}
