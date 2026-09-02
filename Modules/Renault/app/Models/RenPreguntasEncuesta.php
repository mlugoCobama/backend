<?php

namespace Modules\Renault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Renault\Database\Factories\PreguntasEncuestaFactory;

class RenPreguntasEncuesta extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'texto',
        'categoria'
    ];

    protected $table = 'ren_preguntas_encuesta';
    protected $connection = 'autos';

    protected static function newFactory(): PreguntasEncuestaFactory
    {
        //return PreguntasEncuestaFactory::new();
    }

    public function repuestas(){
        return $this->hasMany(RenRespuestasEncuesta::class, 'ren_preguntas_encuesta_id', 'id');
    }
}
