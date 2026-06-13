<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\EntregaTecnicosFactory;

class EntregaTecnicos extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'folio',
        'usuario_entrega',
        'tecnico_id',
        'fecha'
    ];

    protected $table = 'com_entrega_almacen';

    protected static function newFactory(): EntregaTecnicosFactory
    {
        //return EntregaTecnicosFactory::new();
    }
}
