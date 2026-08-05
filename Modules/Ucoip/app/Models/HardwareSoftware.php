<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\HardwareSoftwareFactory;

class HardwareSoftware extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ucoip_hardware_id',
        'ucoip_software_id',
        'activo',
        'fecha_asignacion',
        'fecha_retiro'
    ];

    protected $table = 'ucoip_hardware_software';

    protected static function newFactory(): HardwareSoftwareFactory
    {
        //return HardwareSoftwareFactory::new();
    }

    public function hardware(){
        return $this->belongsTo(HardwarePcModel::class, 'ucoip_hardware_id', 'id');
    }

    public function software(){
        return $this->belongsTo(Software::class, 'ucoip_software_id', 'id');
    }
}
