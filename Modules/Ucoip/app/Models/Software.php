<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\SoftwareFactory;

class Software extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'version', 
        'licencia', 
        'observaciones', 
        'cat_software_id', 
        'usuario_empresa_id',
        'estatus' 
    ];

    protected $table = 'ucoip_software'; 

    protected static function newFactory(): SoftwareFactory
    {
        //return SoftwareFactory::new();
    }

    public function tipoSoftware(){
        return $this->belongsTo(CatSoftware::class, 'cat_software_id','id');
    }
}
