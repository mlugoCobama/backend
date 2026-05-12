<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\DetalleResguardoFactory;

class DetalleResguardo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'resguardo_id',
        'hardware_id',
        'fecha_entrega',
        'fecha_devolucion',
        'observaciones',
        'caracteristicas',
    ];

    protected $table = 'ucoip_detalle_resguardo';

    protected static function newFactory(): DetalleResguardoFactory
    {
        //return DetalleResguardoFactory::new();
    }

    // Relación: pertenece a un resguardo
    public function resguardo()
    {
        return $this->belongsTo(Resguardo::class, 'resguardo_id', 'id');
    }

    // Relación: pertenece a un hardware
    public function hardware()
    {
        return $this->belongsTo(HardwarePcModel::class, 'hardware_id', 'id');
    }
}
