<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\ComOtrosFactory;

class ComOtros extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'com_vendedores_id', 
        'com_catalogo_conceptos_id', 
        'observaciones', 
        'importe',
        'activo',
        'fecha',
    ];
    
    protected $table = 'com_otros';
    protected $connection = 'autos';

    public function vendedor(){
        return $this->belongsTo(Vendedor::class,'com_vendedores_id', 'id');
    }

    public function concepto(){
        return $this->belongsTo(ComCatalogoConceptos::class,'com_catalogo_conceptos_id', 'id');
    }

    protected static function newFactory(): ComOtrosFactory
    {
        //return ComOtrosFactory::new();
    }
}
