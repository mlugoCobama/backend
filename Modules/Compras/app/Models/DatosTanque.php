<?php

namespace Modules\Compras\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\DatosTanqueFactory;

class DatosTanque extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'marca', 
        'anio_fabricacion',
        'capacidad',
        'serie',
        'tipo_medidor',
        'id_sucursal',
        'com_datos_vehiculo_id',
        'activo'
    ];
     /**
     * Nombre de la tabla
     */
    protected $table = 'com_datos_tanque';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Un tanque pertenece a un vehículo.
     */
    public function datos_tanque(){
        return $this->belongsTo(DatosVehiculo::class,'com_datos_vehiculo_id' ,'id');
    }

    protected static function newFactory(): DatosTanqueFactory
    {
        //return DatosTanqueFactory::new();
    }
}
