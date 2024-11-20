<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\NisDatosCFDIFactory;

class NisDatosCFDI extends Model
{
    use HasFactory;

    /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'regimen_fiscal',
        'rfc',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'correo_electronico',
        'telefono_fijo',
        'telefono_movil',
        'fecha_nacimiento',
        'entidad_nacimiento',
        'curp',
        'pais',
        'actividad_preponderante'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'nis_datos_cfdi';
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
