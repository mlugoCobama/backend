<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\CatTiposMantenimientoFactory;

class CatTiposMantenimiento extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'com_cat_tipos_mantenimiento';


    public function SolicitudesCompra(){
        return $this->hasMany(SolicitudesCompra::class);
    }
}
