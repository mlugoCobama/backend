<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\TagsFactory;

class Tags extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'proveedor', 
        'num_tag', 
        'serie', 
        // 'fecha_alta', 
        // 'fecha_venciemiento', 
        // 'saldo_actual', 
        'estatus', 
        'observaciones',
        'intercompania',
        'activo'
    ];

    protected $table = 'com_tags';

    protected static function newFactory(): TagsFactory
    {
        //return TagsFactory::new();
    }

    public function asignaciones(){
        return $this->hasMany(VehiculosTags::class, 'com_id_tags', 'id');
    }
}
