<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\TokensUcoipFactory;

class TokensUcoip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ucoip_ucoip_id', 
        'ucoip_token_agencias_id', 
        'fecha_asignacion', 
        'fecha_retiro', 
        'usuario', 
        'acceso', 
        'contrasenia'
    ];

    protected $table = 'ucoip_tokens_ucoip';

    protected static function newFactory(): TokensUcoipFactory
    {
        //return TokensUcoipControllerFactory::new();
    }

    /**
     *   Una asignación pertenece a un ucoip
     */  
    public function ucoip(){
        return $this->belongsTo(Ucoip::class, 'ucoip_ucoip_id', 'id');
    }

    /**
     *   Una asignación pertenece a un token
     */  
    public function token(){
        return $this->belongsTo(TokenAgencia::class, 'ucoip_token_agencias_id', 'id');
    }
}
