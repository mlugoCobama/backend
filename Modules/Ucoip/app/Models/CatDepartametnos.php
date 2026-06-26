<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\CatDepartametnosFactory;

class CatDepartametnos extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'areas_id'
    ];
    protected $table = 'ucoip_cat_departamentos';

    protected static function newFactory(): CatDepartametnosFactory
    {
        //return CatDepartametnosFactory::new();
    }

    /**
     * Un departamento pertenece a un area
     */
    public function area(){
        return $this->belongsTo(CatAreas::class, 'areas_id', 'id');
    }

    /**
     * Un area tiene muchos puestos
     */
    public function puestos(){
        return $this->hasMany(CatPuestos::class, 'departamentos_id', 'id');
    }



}
