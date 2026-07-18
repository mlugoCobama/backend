<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\IntercambioHardwareFactory;

class IntercambioHardware extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'empresa_origen', 
        'empresa_destino', 
        'fecha_traspaso',
        'ucoip_hardware_id'
    ];

    protected $table = 'ucoip_hardware_intercambios';

    protected static function newFactory(): IntercambioHardwareFactory
    {
        //return IntercambioHardwareFactory::new();
    }

    public function hardware(){
        return $this->belongsTo(HardwarePcModel::class, 'ucoip_hardware_id', 'id');
    }

    public function origen(){
        return $this->belongsTo(CatEmpresas::class, 'empresa_origen', 'id')->select('id',
                            'nombre');
    }

    public function destino(){
        return $this->belongsTo(CatEmpresas::class, 'empresa_destino', 'id')->select('id',
                            'nombre');
    }
}
