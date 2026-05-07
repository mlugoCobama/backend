<?php

namespace Modules\Compras\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\ProveedoresFactory;
use Modules\Ucoip\Models\Servicio;

class Proveedores extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'nombre',
        'rfc',
        'contacto',
        'telefono',
        'localidad',
        'condiciones',
        'servicios',
        'correo',
        'dias_credito',
        'horario_atencion',
        'tiempo_entrega',
        'ti',
        'activo',

    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'com_proveedores';
    
    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }

    public function scopeIsTI ($query) {
        return $query->where('ti', 1);
    }
    
    /*Función para obtener los datos activos
     */
    
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */

    /**
     * Un proveedor tiene varias cotizaciones
     */
    public function Cotizaciones() {
        $this->hasMany(Cotizaciones::class);
    }
    /**
     * Un proveedor pertenece a un expediente
     */
    public function Expediente(){
        return $this->hasOne(ExpedientesProveedores::class, 'proveedores_id', 'id');
    }

    public function CotizacionesProveedores(){
        $this->hasMany(CotizacionesProveedores::class);
    }

    public function contactos(){
        return $this->hasMany(ProveedorContacto::class, 'proveedor_id', 'id');
    }

    public function productos(){
        return $this->hasMany(ProveedorProducto::class, 'proveedor_id', 'id');
    }

    public function datosPago()
    {
        return $this->hasMany(DatosPagoProveedor::class, 'proveedor_id', 'id');
    }

    /**
     * Servicios que le vende a empresas (servicio de TI)
     * Un proveedor tiene varios servicios
     */
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'proveedor_id'. 'id');
    }


}
