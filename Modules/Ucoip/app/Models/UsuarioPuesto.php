<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\UsuarioPuestoFactory;

class UsuarioPuesto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id_usuario',
        'id_puesto',
        'activo'
    ];

    protected $table = 'usuario_puesto'; 

    protected static function newFactory(): UsuarioPuestoFactory
    {
        //return UsuarioPuestoFactory::new();
    }

    public function userGlpi(){
        return $this->belongsTo(GlpiUser::class,'id_usuario', 'id' )
            ->select(['id',
        'name',
        'phone',
        'phone2',
        'mobile',
        'realname',
        'firstname']);
    }

    public function puesto(){
        return $this->belongsTo(CatPuestos::class,'id_puesto', 'id' );
    }
}
