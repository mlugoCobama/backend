<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatosAcumulados extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $fillable = [
        'anio', 'mes', 'valor', 'cat_empresas_id', 'cat_reportes_id'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'datos_acumulados';
    /**
     * Función para obtener los datos activos
     */
    public function scopeActive ($query) {
        return $query->where('estatus', 1);
    }
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Una empresa puede tener varios datos
     */
    public function Empresa() {
        $this->belongsTo(CatEmpresas::class);
    }
    /**
     * Una reporte puede tener varios datos
     */
    public function Reporte() {
        $this->belongsTo(CatReportes::class);
    }
}
