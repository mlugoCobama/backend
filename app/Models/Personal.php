<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */

     protected $fillable = [
        'ventas',
        'usados',
        'refacciones',
        'servicios',
        'admin',
        'apvs',
        'fecha',
        'sucursales_id',
     ];

     public $timestamps = false; 

     /**
      * Nombre de la tabla
      */
    protected $connection = 'dashboard1';
    protected $table = 'personal';
    /*----------------------------------------------
    |Relaciones de bases de datos
    -------------------------------------------------*/
    public function Sucursal() {
        $this->belongsTo(Sucursales::class);
    }
}
