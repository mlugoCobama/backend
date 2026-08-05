<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\TokenAgenciaFactory;

class TokenAgencia extends Model
{
    use HasFactory;
    protected $table = 'ucoip_tokens_agencias';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'token',
        'ucoip_puesto_marca_id',
        'ucoip_cat_empresas_id',
        'activo',
        'observaciones',
    ];

    protected static function newFactory(): TokenAgenciaFactory
    {
        //return TokenAgenciaFactory::new();
    }

    /**
     * Un token pertenece a a un puesto
     */
    public function puestoMarca()
    {
        return $this->belongsTo(CatPuestosMarca::class, 'ucoip_puesto_marca_id');
    }

    /**
     * Un token pertenece a una sucursal
     */
    public function sucursal(){
        return $this->belongsTo(CatEmpresas::class, 'ucoip_cat_empresas_id');
    }

    /**
     * Función para obtener los datos activos
     */

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
}



