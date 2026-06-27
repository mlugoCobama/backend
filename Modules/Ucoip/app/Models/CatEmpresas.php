<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\CatEmpresasFactory;

class CatEmpresas extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'nombre',
        'dominio',
        'intercompania'

    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'ucoip_cat_empresas';

    protected static function newFactory(): CatEmpresasFactory
    {
        //return CatEmpresasFactory::new();
    }

    // Relación: pertenece a un resguardo
    public function inventario()
    {
        return $this->hasMany(HardwarePcModel::class, 'cat_empresa_id', 'id');
    }
}
