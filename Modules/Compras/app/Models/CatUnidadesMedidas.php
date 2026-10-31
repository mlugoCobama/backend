<?php

namespace Modules\Compras\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\CatalogoUnidadesMedidasFactory;

class CatUnidadesMedidas extends Model
{
    use Auditable;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'abreviatura',
        'activo'
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'com_cat_unidades_medida';
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
     * Una unidad de medida tiene varios detalles de solicitud
     */
    public function DetalleSolicitud() {
        return $this->hasMany(DetalleSolicitud::class, 'cat_unidades_medida_id');
    }
    
}
