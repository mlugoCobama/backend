<?php

namespace Modules\Renault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Renault\Database\Factories\RenCatEventosFactory;

class RenCatEventos extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'descripcion'
        ];

    protected $table = 'ren_cat_eventos';
    protected $connection = 'autos';

    protected static function newFactory(): RenCatEventosFactory
    {
        //return RenCatEventosFactory::new();
    }

    public function eventosCita(){
        return $this->hasMany(RenEventosCita::class, 'ren_cat_eventos_id',  'id');
    }
}
