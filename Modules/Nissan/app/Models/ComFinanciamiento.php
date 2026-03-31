<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\ComFinanciamientoFactory;

class ComFinanciamiento extends Model
{
    protected $table = 'com_financiamiento';

    protected $fillable = [
        'no_contrato',
        'fecha_desembolso',
        'numero_factura',
        'monto_financiar',
        'incentivo_dealer',
        'porcentaje_asesor',
        'comision_asesor_pesos',
        'com_vendedores_id',
        'tipo_financiamiento',
        'com_datos_venta_id',
        'observaciones',
        'estatus',
        'comentario',
    ];
    
    protected $connection = 'autos';

    protected $casts = [
        'fecha_desembolso' => 'date',
        'monto_fiananciar' => 'decimal:2',
        'incentivo_dealer' => 'decimal:2',
        'porcentaje_asesor' => 'decimal:2',
        'comision_asesor_pesos' => 'decimal:2',
    ];

    // Relación
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'com_vendedores_id', 'id');
    }

    public function venta()
    {
        return $this->belongsTo(DatosVenta::class, 'com_datos_venta_id', 'id');
    }

        public function scopeActive ($query) {
        return $query->where('activo', 1);
    }

    public function calcularComision()
    {
        if ($this->monto_fiananciar && $this->porcentaje_asesor) {
            return ($this->monto_fiananciar * $this->porcentaje_asesor) / 100;
        }
        return 0;
    }
}
