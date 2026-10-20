<?php

namespace Modules\ParqueVehicular\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Modules\Compras\Models\CatSistemasAuto;
use Modules\Compras\Models\CatTiposMantenimiento;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Models\Proveedores;
use Modules\ParqueVehicular\Database\Factories\OrdenServicioFactory;

class OrdenServicio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'folio',
        'intercompania',
        'com_datos_vehiculos_id',
        'descripcion_falla',
        'observaciones',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'estatus',
        'tipo',
        'tipo_mantenimiento',
        'sistema_auto',
    ];

    protected $table = 'pv_orden_servicio';

    protected static function newFactory(): OrdenServicioFactory
    {
        //return OrdenServicioFactory::new();
    }

        /**
     * Scopes
     */

    public function scopeActive($query){
        return $query->where('activo', 1);
    }


    /**
     * Relaciones
     */

    public function vehiculo(){
        return $this->belongsTo(DatosVehiculo::class, 'com_datos_vehiculos_id', 'id');
    }

    public function proveedorAsignado(){
        return $this->hasOne(OrdenProveedor::class, 'pv_orden_servicio_id', 'id')->where('activo', 1);
    }

    public function proveedores()
    {
        return $this->hasMany(OrdenProveedor::class, 'pv_orden_servicio_id', 'id');
    }

    public function tipoMantenimiento(){
        return $this->belongsTo(CatTiposMantenimiento::class, 'id', 'tipo_mantenimiento');
    }

    public function sistemaMantenimiento(){
        return $this->belongsTo(CatSistemasAuto::class, 'id', 'sistema_auto');
    }

    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        return $query
            // Filtro por rango de fechas en fecha_inicio
            ->when(!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin']), function ($q) use ($filtros) {
                $q->whereBetween('fecha_inicio', [$filtros['fecha_inicio']. ' 00:00:00', $filtros['fecha_fin']. ' 23:59:59']);
            })
            // Filtro por Empresa (usando el campo intercompania)
            ->when(!empty($filtros['intercompania']), function ($q) use ($filtros) {
                $q->where('intercompania', $filtros['intercompania']);
            })
            // Filtro por estatus
            ->when(!empty($filtros['estatus']), function ($q) use ($filtros) {
                $q->whereIn('estatus', $filtros['estatus']);
            })
            // Filtro por Proveedor asignado
            ->when(!empty($filtros['com_proveedores_id']), function ($q) use ($filtros) {
                $q->whereHas('proveedorAsignado', function ($qProv) use ($filtros) {
                    $qProv->where('com_proveedores_id', $filtros['com_proveedores_id']);
                });
            }

            );
    }


}
