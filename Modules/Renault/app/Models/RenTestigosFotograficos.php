<?php

namespace Modules\Renault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RenTestigosFotograficos extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'folio',
        'ruta',
        'nombre',
        'ren_entrada_vehiculo_id'
    ];
     /**
     * Nombre de la tabla
     */
    protected $table = 'ren_testigos_fotograficos';
    /**
     * Conexion que se utilizara
     */
    protected $connection = 'autos';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Varios Testigos son de una entrada de vehiculo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(RenEntradaVehiculo::class);
    }
}
