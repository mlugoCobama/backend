<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\ProveedoresFactory;

class Proveedores extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        //este id no va porque se me paso poer el AI en la bd
        'id',
        'nombre',
        'contacto',
        'telefono',
        'localidad',
        'condiciones',
        'servicios',
        'correo',
        'dias_credito',
        'activo',

    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'proveedores';
    
    public function scopeActive ($query) {
        return $query->where('activo', 1);
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
        $this->belongsTo(ExpedientesProveedores::class);
    }

    public function CotizacionesProveedores(){
        $this->hasMany(CotizacionesProveedores::class);
    }
}
