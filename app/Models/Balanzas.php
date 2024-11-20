<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balanzas extends Model
{
    use HasFactory;
     /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'cta',
        'scta',
        'sscta',
        'ssscta',
        'descripcion',
        'saldo_inicial',
        'debe',
        'haber',
        'saldo_actual',
        'mes',
        'anio',
        'cat_empresas_id',
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'balanzas';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
     /**
     * Una balanza pertenese a una empresa
     */
    public function CatEmpresa() {
        $this->belongsTo(CatEmpresas::class);
    }

}
