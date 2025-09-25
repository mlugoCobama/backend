<?php

namespace Modules\Compras\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\CatSistemasAutoFactory;

class CatSistemasAuto extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
       'categoria',
       'sistema', 
       'descripcion', 
       'activo'
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'com_catalogo_sistemas_auto';


    public function SolicitudesCompra(){
        return $this->hasMany(SolicitudesCompra::class);
    }
}
