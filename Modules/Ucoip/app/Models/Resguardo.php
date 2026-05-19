<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\ResguardoFactory;

class Resguardo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id_usuario_asignado',
        'fecha_inicio',
        'fecha_fin',
        'comentarios',
        'admin_rt',
    ];

    protected $table = 'ucoip_resguardos_ucoip';

    protected static function newFactory(): ResguardoFactory
    {
        //return ResguardoFactory::new();
    }

    // Relación: un resguardo tiene muchos detalles
    public function detalles()
    {
        return $this->hasMany(DetalleResguardo::class, 'ucoip_resguardo_ucoip_id', 'id');
    }
}
