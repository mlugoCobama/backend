<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Ucoip\Database\Factories\PagoProgramadoFactory;

class PagoProgramado extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'servicio_id',
        'fecha_programada',
        'fecha_limite',
        'importe',
        'estado',
        'fecha_pago'
    ];

    protected $table = 'ucoip_pagos_programados';

    // protected static function newFactory(): PagoProgramadoFactory
    // {
    //     // return PagoProgramadoFactory::new();
    // }
}
