<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\DetalleSolicitudFactory;

class DetalleSolicitud extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cantidad',
        'descripcion',
        'observaciones',
        'img_referencia'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'detalle_solicitud';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Un detalle tiene una unidad de medida
     */
    public function CatUnidades()
    {
        return $this->hasOne(CatUnidadesMedidas::class, 'id', 'cat_unidades_medida_id');
    }
    /**
     * Un detalle tiene varias detalles de cotizacion
     */
    public function DetallesCotizacion()
    {
        $this->hasMany(DetallesCotizacion::class);
    }
    /**
     * Un detalle de solicitud pertenece a una solicitud de compra
     */
    public function SolicitudCompra(){
        $this->belongsTo(SolicitudesCompra::class, 'id');
    }

}
