<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComRecargasVehiculos extends Model
{
    use HasFactory;

    protected $table = 'com_recargas_vehiculos';

    protected $fillable = [
        'vehiculo_id',
        'fecha',
        'monto_dispersado',
        'ventas_litros',
        'monto_solicitado',
        'estatus',
        'activo',
        'fecha_dispersion',
        'saldo_actual',
        'com_solicitud_diesel_id',
        'com_vehiculos_toka_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto_dispersado' => 'decimal:2',
        'ventas_litros' => 'decimal:2',
        'monto_solicitado' => 'decimal:2',
        'fecha_dispersion' => 'date',
        'saldo_actual' => 'decimal:2',
    ];


    public function vehiculo()
    {
        return $this->belongsTo(DatosVehiculo::class, 'vehiculo_id', 'id');
    }

    public function vehiculoToka()
    {
        return $this->belongsTo(VehiculosToka::class, 'com_vehiculos_toka_id', 'id');
    }

    public function solicitudDiesel()
    {
        return $this->belongsTo(SolcitudDiesel::class, 'com_solicitud_diesel_id', 'id');
    }
}


