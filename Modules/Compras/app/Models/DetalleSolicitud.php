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
        'img_referencia',
        'confirmado'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'com_detalle_solicitud';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Un detalle tiene una unidad de medida
     */
    public function unidadMedida()
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
        return $this->belongsTo(SolicitudesCompra::class, 'id');
    }

    public function DetalleAutotanque()
    {
        // return $this->hasOne(DetalleAutotanque::class, 'id', 'com_detalle_solicitud_id');
        return $this->hasOne(DetalleAutotanque::class, 'com_detalle_solicitud_id', 'id');
    }

    /**
     * Scopes
     */
    public function scopeConfirmadas($query) {
        return $query->where('confirmado', 1);
    }

}
