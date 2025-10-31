<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\ProveedorContactoFactory;

class ProveedorContacto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'proveedor_id',
        'nombre',
        'correo',
        'telefono',
        'notas',
    ];

    protected $table = 'com_proveedor_contactos';

    protected static function newFactory(): ProveedorContactoFactory
    {
        //return ProveedorContactoFactory::new();
    }

    public function proveedor() {
        return $this->belongsTo(Proveedores::class,  'id', 'proveedor_id');
    }

    public function zona(){
        return $this->hasOne(ProveedorZona::class, 'contacto_id', 'id');
    }
}
