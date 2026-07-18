<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\CatPuestosMarcaFactory;

class CatPuestosMarca extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'puesto'
    ];

    protected $table = 'ucoip_puestos_marca';

    protected static function newFactory(): CatPuestosMarcaFactory
    {
        //return CatPuestosMarcaFactory::new();
    }

    public function tokens(){
        return $this->hasMany(TokenAgencia::class, 'ucoip_puesto_marca_id', 'id');
    }
}
