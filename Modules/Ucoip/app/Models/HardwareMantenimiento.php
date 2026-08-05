<?php

namespace Modules\Ucoip\Models;

use App\Models\UsersGlpi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\HardwareMantenimientoFactory;

class HardwareMantenimiento extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tipo',
        'fecha',
        'falla',
        'diagnostico',
        'comentarios',
        'observaciones',
        'ucoip_hardware_id',
        'id_tecnico',
        'activo'
    ];

    protected $table = 'ucoip_hardware_mantenimientos';

    protected static function newFactory(): HardwareMantenimientoFactory
    {
        //return HardwareMantenimientoFactory::new();
    }

     public function detalles()
    {
        return $this->hasMany(DetalleMantenimiento::class, 'ucoip_hardware_mantenimientos_id');
    }

    public function checklist()
    {
        return $this->hasMany(MantenimientoChecklist::class, 'ucoip_hardware_mantenimiemtos_id');
    }

    public function evidencias()
    {
        return $this->hasMany(EvidenciaManteninimiento::class, 'ucoip_hardware_mantenimiemtos_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(UsersGlpi::class, 'id_tecnico');
    }

    /**
     * Función para obtener los datos activos
     */

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
}
