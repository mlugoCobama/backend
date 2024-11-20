<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\NisDatosVehiculosFactory;

class NisDatosVehiculos extends Model
{
    use HasFactory;

    /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'factura_ampara',
        'expedida_por',
        'fecha_factura',
        'marca',
        'añio_modelo',
        'color_ext',
        'color_int',
        'tipo_version',
        'clase_vehicular',
        'num_serie',
        'num_motor',
        'kilometraje',
        'num_baja_vehicular',
        'edo_emite_baja',
        'fecha_baja',
        'anio_tenencia',
        'placas_baja',
        'num_verificacion',
        'valor_compra',
        'valor_guia_autometria',
        'nombre_a_quien_vende',
        'fecha_factura_final',
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'nis_datos_vehiculos';
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
