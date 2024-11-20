<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NisDatosProveedor extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'folio',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'calle',
        'num_interior',
        'num_exterior',
        'cp',
        'colonia',
        'ciudad',
        'delegacion_municipio',
        'estado',
        'tipo_identificacion',
        'num_identificacion',
        'mes_comprobante_domicilio'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'nis_datos_proveedor';
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
    public function dataCfdi() {
        return $this->hasOne(NisDatosCfdi::class);
    }
    /**
     * Una proveedor solo tiene un vehiculo
     */
    public function dataVehiculo() {
        return $this->hasOne(NisDatosVehiculos::class);
    }
    /**
     * Una proveedor solo tiene un dato bancario
     */
    public function dataBancario() {
        return $this->hasOne(NisDatosBancarios::class);
    }
}
