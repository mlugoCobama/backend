<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\DatosPagoProveedorFactory;

class DatosPagoProveedor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    
    // protected $primaryKey = 'id';
    // public $timestamps = true;

    protected $fillable = [
        'banco',
        'no_cuenta',
        'clave_interbancaria',
        'beneficiario',
        'proveedor_id',
    ];

    protected $table = 'com_datos_pago_proveedor';


    public function proveedor()
    {
        return $this->belongsTo(Proveedores::class, 'proveedor_id', 'id');
    }




    protected static function newFactory(): DatosPagoProveedorFactory
    {
        //return DatosPagoProveedorFactory::new();
    }
}
