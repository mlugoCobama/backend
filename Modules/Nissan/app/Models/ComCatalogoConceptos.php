<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\ComCatalogoConceptosFactory;

class ComCatalogoConceptos extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'tipo',
        'activo'
    ];
    
    protected $table = 'com_catalogo_conceptos';
    protected $connection = 'autos';

    public function otros(){
        return $this->hasMany(ComOtros::class, 'com_catalogo_conceptos_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('activo', 1);
    }

    public function scopeIngresos($query)
    {
        return $query->where('tipo', 'Ingreso');
    }

    public function scopeDescuentos($query)
    {
        return $query->where('tipo', 'Descuento');
    }

    protected static function newFactory(): ComCatalogoConceptosFactory
    {
        //return ComCatalogoConceptosFactory::new();
    }
}
