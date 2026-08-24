<?php

namespace Modules\Renault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Renault\Database\Factories\RenEventosCitaFactory;

class RenEventosCita extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ren_citas_servicio_id',
        'inicio_evento',
        'fin_evento',
        'observaciones',
        'ren_cat_eventos_id'
    ];

    protected $table = 'ren_eventos_cita';
    protected $connection = 'autos';

    protected static function newFactory(): RenEventosCitaFactory
    {
        //return RenEventosCitaFactory::new();
    }

    public function cita(){
        return $this->belongsTo(RenCitasServicio::class, 'ren_citas_servicio_id', 'id');
    }

    public function catEvento(){
        return $this->belongsTo(RenCatEventos::class, 'ren_cat_eventos_id', 'id');
    }
}
