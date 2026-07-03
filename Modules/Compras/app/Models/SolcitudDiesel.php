<?php

namespace Modules\Compras\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\SolcitudDieselFactory;

class SolcitudDiesel extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [ 
        'inicio_periodo',
        'fin_periodo',
        'precio_combustible',
        'folio',
        'auto_gg',
        'auto_ga',
        'auto_go',
        'usuario_solicita',
        'fecha',
        'estatus',
        'activo',
        'fecha_dispersion',
        'empresa'
    ];


    protected $table = 'com_solicitud_diesel';

    protected static function newFactory(): SolcitudDieselFactory
    {
        //return SolcitudDieselFactory::new();
    }

    public function detalles(){
        return $this->hasMany(ComRecargasVehiculos::class, 'com_solicitud_diesel_id', 'id');
    }
}
