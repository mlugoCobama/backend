<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\VendedorFactory;

class Vendedor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tipo',
        'porcentaje_apv',
        'nro_vendedor_as',
        'agencia',
    ];

    protected $table = 'com_vendedores';

    protected $connection = 'autos';


    // Relación: un vendedor puede tener muchas ventas
    public function datosVentas()
    {
        return $this->hasMany(DatosVenta::class, 'vendedor');
    }



    protected static function newFactory(): VendedorFactory
    {
        //return VendedorFactory::new();
    }
}
