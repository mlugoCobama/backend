<?php

namespace Modules\Capacitaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Capacitaciones\Database\Factories\PermissionHasPuestoFactory;

class PermissionHasPuesto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'puesto_id',
        'permiso_id'
    ];
    
    /**
     * Nombre de la tabla
     */
    protected $table = 'permission_has_puesto';

    public function permiso(){
        return $this->belongsTo('permissions', 'permiso_id', 'id');
    }

    protected static function newFactory(): PermissionHasPuestoFactory
    {
        //return PermissionHasPuestoFactory::new();
    }
}
