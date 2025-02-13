<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\CatalogoUnidadesMedidasFactory;

class CatUnidadesMedidas extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'abreviatura'
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'cat_unidades_medida';
    /**
     * Función para obtener los datos activos
     */
    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
    /*

     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
     /**
     * Una unidad  de medida pertenece a  varios detalle de solicitud
     */
    public function DetalleSolicitud() {
        $this->belongsTo(DetalleSolicitud::class);
    }
    
}
