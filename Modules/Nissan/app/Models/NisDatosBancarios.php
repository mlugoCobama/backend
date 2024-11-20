<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\NisDatosBancariosFactory;

class NisDatosBancarios extends Model
{
    use HasFactory;

    /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'costo_adquisicion',
        'banco',
        'cuenta',
        'clabe',
        'sucursal',
        'convenio',
        'referencia',
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'nis_datos_bancarios';
    /**
     * Conexion que se utilizara
     */
    protected $connection = 'autos';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Una proveedor solo tiene un cfdi
     */
    public function dataProveedor() {
        return $this->belongsTo(NisDatosCfdi::class);
    }
}
