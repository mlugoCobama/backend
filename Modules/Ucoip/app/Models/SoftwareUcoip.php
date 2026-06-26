<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\SoftwareUcoipFactory;

class SoftwareUcoip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ucoip_ucoip_id',
        'ucoip_software_id', 
        'fecha_asignacion',
        'fecha_retiro'
    ];

    protected $table = 'ucoip_software_ucoip'; 

    protected static function newFactory(): SoftwareUcoipFactory
    {
        //return SoftwareUcoipFactory::new();
    }

    public function licencia(){
        return $this->belongsTo(Software::class, 'ucoip_software_id', 'id');
    }

    public function ucoip(){
         return $this->belongsTo(Ucoip::class, 'ucoip_ucoip_id', 'id');
    }
}
