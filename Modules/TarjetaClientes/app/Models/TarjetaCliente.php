<?php

namespace Modules\TarjetaClientes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\TarjetaClientes\Database\Factories\TarjetaClienteFactory;

class TarjetaCliente extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
        protected $table = 'tarjeta_clientes';

    protected $fillable = [
        'Folio',
        'Fecha',
        // Step 1
        'agencia', 'asesor_ventas', 'no_sicop',

        // Step 2
        'nombre_cliente', 'direccion', 'ciudad', 'estado',

        // Step 3
        'email_personal', 'email_trabajo', 'telefono_principal',
        'telefono_secundario', 'telefono_adicional',

        // Step 4
        'tiene_cita', 'tipo_contacto', 'cual_publicidad',

        // Step 5
        'servicio', 'notas_apv', 'notas_gv',

        // Step 6
        'cliente_quiere', 'anio', 'modelo', 'estilo',
        'color', 'stock_vin', 'equipo_particular',

        // Step 7
        'anio_vehiculo', 'modelo_vehiculo', 'estilo_vehiculo',
        'color_vehiculo', 'ac', 'pw', 'pl', 'cruise', 'tilt',
        'auto', 'x4x4', 'cd', 'sat', 'navi', 'kilometraje',
        'vin', 'costo_pagar', 'acv', 'telefono_banco',
    ];

    // Casting automático (opcional, pero útil)
    protected $casts = [
        'ac' => 'boolean',
        'pw' => 'boolean',
        'pl' => 'boolean',
        'cruise' => 'boolean',
        'tilt' => 'boolean',
        'auto' => 'boolean',
        'x4x4' => 'boolean',
        'cd' => 'boolean',
        'sat' => 'boolean',
        'navi' => 'boolean',
    ];
    
    protected static function newFactory(): TarjetaClienteFactory
    {
        //return TarjetaClienteFactory::new();
    }
}
