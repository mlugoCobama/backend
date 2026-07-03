<?php

namespace Modules\Compras\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Compras\Database\Factories\DatosVehiculoFactory;
use Modules\Compras\Models\DatosTanque;
use Modules\Compras\Models\DetalleAutotanque;
use Modules\Compras\Models\ObservacionVehiculo;

class DatosVehiculo extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'marca',
        'submarca',
        'modelo',
        'no_serie',
        'placas',
        'tipo',
        'id_sucursal',
        'id',
        'activo',
        'tipo_combustible',
        'nro_economico',
        'id_cre',
        'ruta',
        'estatus',
        'propietario',
        'categoria',
        'gps',
        'limite', // limite del tag
        'num_tarjeta_toka',
        'num_tag',
        'unit_id_gps', 
        'capacidad_combustible',
        'rendimiento_x_litro'
    ];
     /**
     * Nombre de la tabla
     */
    protected $table = 'com_datos_vehiculos';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Un tanque pertenece a un vehículo.
     */
    public function datos_tanque(){
        return $this->hasOne(DatosTanque::class,'com_datos_vehiculo_id' ,'id' );
    }

    public function DetalleAutotanque(){
        return $this->hasMany(DetalleAutotanque::class,'com_datos_vehiculos_id' ,'id' );
    }

    public function comentariosVehiculos(){
        return $this->hasMany(ObservacionVehiculo::class,'datos_vehiculo_id' ,'id' );
    }

    /**
     * Un vehiculo tiene muchos datos de gps
     */
    public function datosGps(){
        return $this->hasMany(DatosGps::class, 'com_datos_vehiculos_id', 'id');
    }

    public function asigancionTag(){
        return $this->hasMany(VehiculosTags::class, 'com_datos_vehiculos_id', 'id');
    }

    /**
     * Función para obtener los datos activos
     */
    public function scopeAutorizadas ($query) {
        return $query->where('estatus', 1);
    }

    public function scopeActivas ($query) {
        return $query->where('activo', 1);
    }

    protected static function newFactory(): DatosVehiculoFactory
    {
        //return DatosVehiculoFactory::new();
    }
}
