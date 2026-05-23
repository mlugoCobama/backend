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
        'estaciones',
        'tipo',
        'descripcion',
        'created_at',
        'updated_at',
    ];

    protected $table = 'vol_reporte_volumenes';

    protected static function newFactory(): ReporteVolumenFactory
    {
        //return ReporteVolumenFactory::new();
    }
}
