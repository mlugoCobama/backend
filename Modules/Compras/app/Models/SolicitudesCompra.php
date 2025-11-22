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
        'c_c',
        'usuario_solicita',
        'usuario_destino',
        'motivo',
        'fecha',
        'estatus',
        'razon_cancelacion',
        'activo',
        'empresa',
        'tipo',
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
        return $query->whereIn('tipo', [1, 3]);
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
}
