<?php

namespace Modules\Renault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Renault\Database\Factories\RespuestasEncuestaFactory;

class RenRespuestasEncuesta extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ren_encuesta_cita_id',
        'ren_preguntas_encuesta_id',
        'motivo',
        'puntuacion'
    ];

    protected $table = 'ren_respuestas_encuesta';
    protected $connection = 'autos';

    protected static function newFactory(): RespuestasEncuestaFactory
    {
        //return RespuestasEncuestaFactory::new();
    }

    public function pregunta(){
       return $this->belongsTo(RenPreguntasEncuesta::class, 'ren_preguntas_encuesta_id', 'id');
    }

    public function encuesta(){
       return $this->belongsTo(RenPreguntasEncuesta::class, 'ren_encuesta_cita_id', 'id');
    }
}
