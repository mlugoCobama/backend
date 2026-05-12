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
        'usuario_asignado',
        'fecha_inicio',
        'fecha_fin',
        'comentarios',
        'admin_rt',
    ];

    protected $table = 'ucoip_resguardo';

    protected static function newFactory(): ResguardoFactory
    {
        //return ResguardoFactory::new();
    }

    // Relación: un resguardo tiene muchos detalles
    public function detalles()
    {
        return $this->hasMany(DetalleResguardo::class);
    }
}
