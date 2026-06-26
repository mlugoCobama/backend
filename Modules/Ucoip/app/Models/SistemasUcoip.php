<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\SistemasUcoipFactory;

class SistemasUcoip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'username',
        'password',
        'observaciones',
        'fecha_asignacion',
        'fecha_fin',
        'activo',
        'ucoip_cat_sistemas_id',
        'ucoip_ucoip_id'
    ];

    protected $table =  'ucoip_sitemas_ucoip';


    protected static function newFactory(): SistemasUcoipFactory
    {
        //return SistemasUcoipFactory::new();
    }

    public function ucoip(){
        return $this->belongsTo(Ucoip::class, 'ucoip_ucoip_id', 'id');
    }

    public function sistema(){
        return $this->belongsTo(CatSistemas::class, 'ucoip_cat_sistemas_id', 'id');
    }
}
