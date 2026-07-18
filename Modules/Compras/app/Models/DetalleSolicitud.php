<?php

namespace Modules\Compras\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\DetalleSolicitudFactory;
use Modules\Macro\Models\Almacen;

class DetalleSolicitud extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cantidad',
        'descripcion',
        'observaciones',
        'img_referencia',
        'confirmado',
        'estatus_almacen',
        'recuperable',
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
        return $this->hasMany(DetallesCotizacion::class);
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

    public function Almacen()
    {
        // return $this->hasOne(DetalleAutotanque::class, 'id', 'com_detalle_solicitud_id');
        return $this->hasOne(Almacen::class, 'com_detalle_solicitud_id', 'id');
    }

    public function almacenCompras(){
        return $this->hasOne(AlmacenCompras::class, 'com_detalle_solicitud_id', 'id');
    }

    public function cotizacionSeleccionada()
    {
        return $this->hasOne(
                DetallesCotizacion::class,
                'detalle_solicitud_id',
                'id'
            )->whereHas('CotizacionesProveedores', function ($q) {
                $q->where('seleccionado', 1);
            });
    }

    /**
     * Scopes
     */
    public function scopeConfirmadas($query) {
        return $query->where('confirmado', 1);
    }

    public function scopePendientes($query) {
        return $query->where('estatus_almacen', 0);
    }

    public function scopeAlmacenado($query) {
        return $query->where('estatus_almacen', 1);
    }

    public function scopeUtlizado($query) {
        return $query->where('estatus_almacen', 2);
    }

}
