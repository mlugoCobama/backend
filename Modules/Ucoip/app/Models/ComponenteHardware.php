<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Ucoip\Database\Factories\ComponenteHarwdwareFactory;

class ComponenteHardware extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'ucoip_hardware_id',
        'tipo',
        'descripcion',
        'cantidad',
        'costo_unitario',
        'costo_total',
        'componente_reemplazado',
        'fecha_instalacion',
        'fecha_retiro',
        'observaciones',
        'created_at',
        'updated_at',
        'com_detalle_solicitud_id',
  ];

  protected $table = 'ucoip_hardware_componentes'; 

    protected static function newFactory(): ComponenteHarwdwareFactory
    {
        //return ComponenteHarwdwareFactory::new();
    }

    /**
     * Un componente de hardware pertenece a un detalle de solicitud
     */
    public function detalle(){
        return $this->belongsTo(DetalleSolicitud::class, 'com_detalle_solicitud_id', 'id');
    }

    /**
     * Un componente de hardware pertenece a un hardware padre (PC, Servidor, Nas, Etc)
     */

    public function hardwarePadre(){
        return $this->belongsTo(HardwarePcModel::class, 'ucoip_hardware_id', 'id');
    }
}
