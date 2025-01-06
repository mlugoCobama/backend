<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\CotizacionesProveedoresFactory;

class CotizacionesProveedores extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'proveedores_id',
        'cotizaciones_id',
        'ruta',
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'cotizaciones_proveedores';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Un registro pertenece a un proveedor.
     */
    public function DatosProveedor(){
        return $this->belongsTo(proveedores::class,'proveedores_id' ,'id');
    }
    /**
     * Un registro pertenece a una cotización.
     */
    public function DatosCotizacion(){
        $this->belongsTo(Cotizaciones::class);
    }

    /**
     * Un registro tiene varios detalles de cotización.
     */
    public function DetallesCotizacion(){
        $this->hasMany(DetallesCotizacion::class);
    }

    protected static function newFactory(): CotizacionesProveedoresFactory
    {
        //return CotizacionesProveedoresFactory::new();
    }
}
