<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\ProveedorZonaFactory;

class ProveedorZona extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'contacto_id',
        'nombre_zona',
        'estados',
        'activo',
    ];

     /**
     * Nombre de la tabla
     */
    protected $table = 'com_proveedor_zonas';


    protected static function newFactory(): ProveedorZonaFactory
    {
        //return ProveedorZonaFactory::new();
    }


    public function contacto() {
        return $this->belongsTo(ProveedorContacto::class,  'id', 'contacto_id');
    }

    // public function contacto(){
    //     return $this->hasOne(ProveedorContacto::class, 'proveedor_zona_id', 'id' );
    // }
    
}
