<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\CatPuestosFactory;

class CatPuestos extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'departamentos_id',
    ];

    protected $table = 'ucoip_cat_puestos';

    protected static function newFactory(): CatPuestosFactory
    {
        //return CatPuestosFactory::new();
    }

    /**
     * Un puesto pertenece a un departamento
     */
    public function departamento(){
       return $this->belongsTo(CatDepartametnos::class, 'departamentos_id', 'id');
    }
}
