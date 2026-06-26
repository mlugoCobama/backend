<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\CatSoftwareFactory;

class CatSoftware extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tipo',
        'icono'
    ];

    protected $table = 'ucoip_cat_software';

    protected static function newFactory(): CatSoftwareFactory
    {
        //return CatSoftwareFactory::new();
    }

    public function licencias(){
        return $this->hasMany(Software::class, 'cat_software_id',  'id');
    }

    public function licenciasDisponible()
    {
        return $this->hasMany(Software::class, 'cat_software_id', 'id')
                    ->where('estatus', 1)
                    ->select('id',
                            'version', 
                            'licencia', 
                            'observaciones', 
                            'cat_software_id', 
                            'usuario_empresa_id',
                            'estatus');
    }

}
