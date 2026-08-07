<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\ExhibicionesRecargasFactory;

class ExhibicionesRecargas extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'numero_exhibicion',
        'saldo_actual_previo',
        'monto_dispersado',
        'estatus',
        'fecha_dispersion',
        'com_recargas_vehiculos_id',
        'guardada',
        'notificada',
        'dispersada',
    ];

    protected $table = 'com_exhibiciones_recargas';

    protected static function newFactory(): ExhibicionesRecargasFactory
    {
        //return ExhibicionesRecargasFactory::new();
    }
}
