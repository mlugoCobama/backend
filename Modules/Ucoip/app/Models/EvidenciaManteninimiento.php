<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\EvidenciaManteninimientoFactory;

class EvidenciaManteninimiento extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tipo',
        'ruta',
        'ucoip_hardware_mantenimiemtos_id'
    ];

    protected $table = 'ucoip_evidencia_mantenimiento';

    protected static function newFactory(): EvidenciaManteninimientoFactory
    {
        //return EvidenciaManteninimientoFactory::new();
    }
}
