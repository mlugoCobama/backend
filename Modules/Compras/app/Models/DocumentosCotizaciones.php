<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\DocumentosCotizacionesFactory;

class DocumentosCotizaciones extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ruta'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'DocumentosCotizaciones';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
     /**
     * Una documento de cotizacion pertenece a una cotizacion
     */
    public function Cotizacion() {
        $this->belongsTo(Cotizaciones::class);
    }

}
