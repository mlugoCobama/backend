<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\VehiculosTagsFactory;

class VehiculosTags extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'com_id_datos_vehiculos', 
        'com_id_tags', 
        'fecha_inicio', 
        'fecha_fin',
        'activo'
    ];

    protected $table = 'com_vehiculos_tag';

    protected static function newFactory(): VehiculosTagsFactory
    {
        //return VehiculosTagsFactory::new();
    }

    public function vehiculos(){
        return $this->belongsTo(DatosVehiculo::class, 'com_id_datos_vehiculos', 'id');
    }

    public function tags(){
        return $this->belongsTo(Tags::class, 'com_id_tags', 'id');
    }
}
