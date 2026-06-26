<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\HardwareUcoipFactory;

class HardwareUcoip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ucoip_hardware_id', 
        'ucoip_ucoip_id', 
        'glpi_user_id', 
        'fecha_inicio', 
        'fecha_fin', 
        'estatus'
    ];

    protected $table = 'ucoip_hardware_ucoip';

    protected static function newFactory(): HardwareUcoipFactory
    {
        //return HardwareUcoipFactory::new();
    }

    public function hardware(){
       return $this->belongsTo(HardwarePcModel::class, 'ucoip_hardware_id',  'id');
    }

    // public function userUcoip(){
    //     $this->belongsTo(HardwarePcModel::class,  'ucoip_hardware_id',  'id');
    // }
 
    public function userGlpi(){
       return $this->belongsTo(GlpiUser::class,  'glpi_user_id',  'id');
    }
}
