<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngresosGastos extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'contable',
        'ajustes_ingresos',
        'conciliable',
        'planeacion_favor',
        'ajuste_auditoria',
        'rt',
        'neto',
        'mes',
        'anio',
        'cat_empresas_id',
        'cat_ingresos_gastos_id',
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'ingresos_gastos';
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
