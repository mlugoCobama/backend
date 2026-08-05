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
        'estatus',
        'activo',
        'tipo_licencia',
        'cuenta',
        'pass_cuenta',
        'fecha_adquisicion',
        'empresa'
    ];

    protected $table = 'ucoip_software';

    protected static function newFactory(): SoftwareFactory
    {
        //return SoftwareFactory::new();
    }

    public function tipoSoftware(){
        return $this->belongsTo(CatSoftware::class, 'cat_software_id','id');
    }

    public function sucursal(){
        return $this->belongsTo(CatEmpresas::class, 'empresa' ,'id');
    }

    /**
     * Función para obtener los datos activos
     */

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
}
