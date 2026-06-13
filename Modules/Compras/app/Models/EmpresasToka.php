<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\EmpresasTokaFactory;

class EmpresasToka extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'intercompania',
        'no_cliente',
        'nombre_empresa',
    ];

    protected $table = 'com_empresas_toka';

    protected static function newFactory(): EmpresasTokaFactory
    {
        //return EmpresasTokaFactory::new();
    }

    public function tarjetas(){
        return $this->hasMany(TarjetasToka::class, 'com_empresas_toka_id', 'id');
    }

    public function tarjetasDisponibles()
    {
        return $this->hasMany(TarjetasToka::class, 'com_empresas_toka_id', 'id')
                    ->where('estatus', '0');
    }

    
}
