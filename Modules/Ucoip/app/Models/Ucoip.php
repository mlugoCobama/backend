<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Ucoip\Database\Factories\UcoipFactory;

class Ucoip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ucoip',
        'contrasenia',
        'usuario_as',
        'ip',
        'extension',
        'movil',
        'activo',
        'user_id', 
        'ucoip_cat_puestos',
        'cat_empresa_id'
        ];
    
        protected $table = 'ucoip_ucoip';

    protected static function newFactory(): UcoipFactory
    {
        //return UcoipFactory::new();
    }

    public function puesto(){
        return $this->belongsTo(CatPuestos::class, 'ucoip_cat_puestos', 'id');
    }
}
