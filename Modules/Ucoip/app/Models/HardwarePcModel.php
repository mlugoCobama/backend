<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HardwarePcModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "marca",
        "modelo",
        "no_serie",
        "tipo",
        "mac",
        "memoria_ram",
        "disco_duro",
        "procesador",
        "caracteristicas",
        "observaciones",
        "estado",
        "cat_hardware_id",
        "cat_empresa_id",
        "almacen_compra_id"
    ];
    /**
     * Campo "tipo" se ocupa para diferenciar el tipo de cpu
     * 1. CPU de Marca
     * 2. CPU Armada
     * 3. Laptop
     * 4. Terminal
     * 5. All In One
     * 6. Laptop Consul
     */
    public $timestamps = true;
    /**
     * Nombre de la tabla
     */
    protected $table = 'ucoip_hardware';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Un hardware es de un tipo
     */
    public function Tipo() {
        return  $this->hasOne(CatHardwareModel::class, 'id', 'cat_hardware_id');
    }

    public function tipoHardware() {
        return  $this->belongsTo(CatHardwareModel::class, 'cat_hardware_id', 'id');
    }

    public function empresa(){
        return $this->belongsTo(CatEmpresas::class, 'cat_empresa_id', 'id');
    }

}
