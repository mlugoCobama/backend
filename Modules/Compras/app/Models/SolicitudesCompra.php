<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\SolicitudesCompraFactory;
use App\Traits\Auditable;

class SolicitudesCompra extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'folio',
        'usuario_destino',
        'motivo',
        'fecha',
        'c_c',
        'activo',
        'usuario_solicita',
        'empresa',
        'estatus',
        'observaciones',
        'com_cat_sistemas_auto_id',
        'com_cat_tipos_mantenimiento_id',
        'folio_requisicion',
        'razon_cancelacion',
        'tipo',
        'requiere_anticipo',
        'motivo_revision',
        'auto_admin',
        'auto_gg',
        'auto_macro'
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'com_solicitudes_compra';
    /**
     * Función para obtener los datos activos
     */
    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
    /**
     * Función para obtener las compras comunes
     */
    public function scopeCompras($query) {
        return $query->where('tipo', 1);
    }
    /**
     * Función para obtener las compras de macro taller
     */
    public function scopeMacrotaller($query) {
        return $query->where('tipo', 2);
    }
    /**
     * Función para obtener las compras de macro taller
     */
    public function scopeRtecnologicos($query) {
        return $query->where('tipo', 3);
    }
    /**
     * Función para obtener las compras en estatus solicitada
     */
    public function scopeAutorizadas($query) {
        return $query->where('estatus', '>', 1);
    }

    public function scopeAdministrador($query) {
        return $query->whereIn('tipo', [1, 3, 4]);
    }

    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Una solicitud tiene varios detalles
     */
    public function DetallesSolicitud() {
        return $this->hasMany(DetalleSolicitud::class, 'solicitudes_compra_id');
    }

    /**
     * Una solicitud tiene varias cotizaciones
     */
    public function Cotizaciones(){
        return $this->hasMany(Cotizaciones::class, 'solicitudes_compra_id', 'id');
    }

    /**
     * Una solicitud tiene una orden de trabajo
     */
    public function OrdenTrabajo()
    {
        return $this->hasOne(OrdenTrabajo::class, 'com_solicitudes_compra_id', 'id');
    }

    public function DestinoVehiculo(){
        return $this->belongsTo(DatosVehiculo::class, 'usuario_destino', 'id' );
    }

    public function TipoMantenimiento(){
        return $this->hasOne(CatTiposMantenimiento::class, 'id', 'com_cat_tipos_mantenimiento_id' );
    }

    public function SistemaMantenimiento(){
        return $this->hasOne(CatSistemasAuto::class, 'id', 'com_cat_sistemas_auto_id', );
    }
}
