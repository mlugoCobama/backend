<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\RecursosRedUcoipFactory;

class RecursosRedUcoip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'equipo_id', 
        'valor', 
        'nivel_restrictivo', 
        'observaciones', 
        'fecha_asignacion', 
        'fecha_retiro',  
        'ucoip_ucoip_id', 
        'ucoip_cat_recursos_id'
    ];

    protected $table =  'ucoip_recursos_red';

    protected static function newFactory(): RecursosRedUcoipFactory
    {
        //return RecursosRedUcoipFactory::new();
    }

    public function ucoip(){
        return $this->belongsTo(Ucoip::class, 'ucoip_ucoip_id', 'id');
    }

    public function recursoRed(){
        return $this->belongsTo(CatRecursos::class, 'ucoip_cat_recursos_id', 'id');
    }
}
