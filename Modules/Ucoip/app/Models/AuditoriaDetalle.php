<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\AuditoriaDetalleFactory;

class AuditoriaDetalle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
     protected $table = 'ucoip_auditoria_detalles';

    protected $fillable = [
        'auditoria_id',
        'tipo',
        'referencia_id',
        'resultado',
        'datos',
        'observaciones',
    ];

    protected static function newFactory(): AuditoriaDetalleFactory
    {
        //return AuditoriaDetalleFactory::new();
    }

    protected $casts = [
        'datos' => 'array',
    ];

    public function auditoria()
    {
        return $this->belongsTo(
            Auditoria::class,
            'auditoria_id'
        );
    }
}
