<?php

namespace Modules\Compras\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\CotizacionesProveedoresFactory;

class CotizacionesProveedores extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'proveedores_id',
        'cotizaciones_id',
        'ruta',
        'seleccionado',
        'autorizado',
        'contacto_id'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'com_cotizaciones_proveedores';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Un registro pertenece a un proveedor.
     */
    public function datos_proveedor(){
        return $this->belongsTo(Proveedores::class,'proveedores_id' ,'id');
    }

    //relaciones test
    public function proveedores_id(){
        return $this->belongsTo(Proveedores::class,'proveedores_id' ,'id');
    }

    public function detalles(){
        return $this->hasMany(DetallesCotizacion::class,'cotizaciones_proveedores_proveedores_id' ,'id');
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

    protected static function newFactory()
    {
        //return CotizacionesProveedoresFactory::new();
    }

    public function scopeSeleccionado ($query) {
        return $query->where('seleccionado', 1);
    }
}
