<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\MantenimientoChecklistFactory;

class MantenimientoChecklist extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cat_checklist_mantenimiento_id',
        'ucoip_hardware_mantenimiemtos_id',
        'completado'
    ];
    protected $table = 'ucoip_mantenimiento_check';

    protected static function newFactory(): MantenimientoChecklistFactory
    {
        //return MantenimientoChecklistFactory::new();
    }

    public function catalogo()
    {
        return $this->belongsTo(CatCheckListMantenimientos::class, 'cat_checklist_mantenimiento_id');
    }
}
