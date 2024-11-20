<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\ExpedientesProveedoresFactory;

class expedientesProveedores extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'constancia_fiscal',
        'ine',
        'comprobante_domicilio',
        'estado_cuenta',
        'acta_constitutiva',
        'proveedores_id',
        'poder_notarial'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'expedientes_proveedor';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Una proveedor tiene varios documentos
     */
    public function Datos() {
        $this->hasMany(proveedores::class);
    }

    protected static function newFactory(): ExpedientesProveedoresFactory
    {
        //return ExpedientesProveedoresFactory::new();
    }
}
