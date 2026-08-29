<?php

namespace Modules\Volumetricos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Volumetricos\Database\Factories\AcusesReporteFactory;

class AcusesReporte extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ruta',
        'tipo',
        'fecha',
        'activo',
        'created_at',
        'updated_at',
        'vol_reporte_volumenes_id'
    ];

    protected $table = 'vol_acuses_reporte';

    protected static function newFactory(): AcusesReporteFactory
    {
        //return AcusesReporteFactory::new();
    }

    public function reporte(){
       return $this->belongsTo(ReporteVolumen::class, 'vol_reporte_volumenes_id', 'id');
    }
}
