<?php

namespace Modules\Volumetricos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Volumetricos\Database\Factories\ReporteVolumenFactory;

class ReporteVolumen extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'empresa',
        'ruta_archivo',
        'ruta_plantilla',
        'uuid_plantilla',
        'estaciones',
        'tipo',
        'activo',
        'fecha_reporte',
        'descripcion',
        'comentarios',
        'activo',
        'created_at',
        'updated_at',
    ];

    protected $table = 'vol_reporte_volumenes';

    protected static function newFactory(): ReporteVolumenFactory
    {
        //return ReporteVolumenFactory::new();
    }

    /**
     * Función para obtener los datos activos
     */
    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }

    public function acuses(){
      return  $this->hasMany(AcusesReporte::class, 'vol_reporte_volumenes_id', 'id');
    }
}
