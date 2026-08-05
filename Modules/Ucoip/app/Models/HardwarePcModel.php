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
        "almacen_compra_id",
        "no_inventario",
        "estado_fisico",
        'activo'
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

    /**
     * Und hardware tiene un tipo
     */
    public function tipoHardware() {
        return  $this->belongsTo(CatHardwareModel::class, 'cat_hardware_id', 'id');
    }

    /**
     * Un hardware tiene una empresa
     */
    public function empresa(){
        return $this->belongsTo(CatEmpresas::class, 'cat_empresa_id', 'id');
    }

    /**
     * Un hardware tiene muchas asignaciones
     */
    public function asignacion(){
        return $this->hasMany(HardwareUcoip::class, 'ucoip_hardware_id', 'id');
    }

    /**
     * Un hardware tiene muchas asignaciones
     */
    public function mantenimientos(){
        return $this->hasMany(HardwareMantenimiento::class, 'ucoip_hardware_id', 'id');
    }

    /**
     * Un hardware puede tener muchos cambios
     */
    public function cambiosHardware(){
        return $this->hasMany(ComponenteHardware::class,'ucoip_hardware_id', 'id');
    }

    /**
     * Un hardware puede tener muchos intercambios
     */
    public function intercambios(){
        return $this->hasMany(IntercambioHardware::class,'ucoip_hardware_id', 'id')
        ->select('id',
                'empresa_origen',
                'empresa_destino',
                'fecha_traspaso',
                'ucoip_hardware_id'
                );
    }


    /**Un hgardware tien una asignacion actual */
    public function asignacionActual()
    {
        return $this->hasOne(HardwareUcoip::class, 'ucoip_hardware_id', 'id')
                    ->whereNull('fecha_fin')
                    ->latestOfMany();
    }



    public function scopeUsuarios($query)
    {
        return $query->whereHas('Tipo', function ($q) {
            $q->usuarios();
        });
    }

    public function scopeInfraestructura($query)
    {
        return $query->whereHas('Tipo', function ($q) {
            $q->infraestructura();
        });
    }


        /**
     * Función para obtener los datos activos
     */

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }

}
