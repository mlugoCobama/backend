<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\CatAreasFactory;

class CatAreas extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [ 'nombre'];
    protected $table = 'ucoip_cat_areas';

    protected static function newFactory(): CatAreasFactory
    {
        //return CatAreasFactory::new();
    }

    /**
     * Un area tiene muchos departamentos
     */
    public function departamentos(){
        return $this->hasMany(CatDepartametnos::class,'areas_id', 'id');
    }
}
