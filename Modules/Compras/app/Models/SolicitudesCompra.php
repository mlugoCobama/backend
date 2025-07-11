<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\SolicitudesCompraFactory;

class SolicitudesCompra extends Model
{
    use HasFactory;

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
        'activo',
        'empresa'
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

    public function scopeCompras($query) {
        return $query->where('tipo', 1);
    }

    public function scopeMacrotaller($query) {
        return $query->where('tipo', 2);
    }

    public function scopeAutorizadas($query) {
        return $query->where('estatus', '>', 1);
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

    public function Cotizaciones(){
        return $this->hasMany(Cotizaciones::class);
    }

    public function OrdenTrabajo()
    {
    return $this->hasOne(OrdenTrabajo::class, 'com_solicitudes_compra_id', 'id');
    }


    //TODO RELACIONARLO CON LA BASE DE USUARIOS Y EMPRESAS
    /**
     * Una solicitud pertenece a un usuario
     */

}
