<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\ComTipoVendedorFactory;

class ComTipoVendedor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
     protected $fillable = [
        'nombre',
        'porcentaje_com_factura',
        'porcentaje_comisiones',
        'activo'
    ];

    protected $table = 'com_tipo_vendedor';

    protected $connection = 'autos';


    public function vendedores()
    {
        return $this->hasMany(Vendedor::class, 'com_departamentos_id');
    }

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }

    protected static function newFactory(): ComTipoVendedorFactory
    {
        //return ComTipoVendedorFactory::new();
    }

        /**
     * Crear un nuevo registro
     */
    public static function crear(array $data)
    {
        $tipoVendedor = new self();
        $tipoVendedor->nombre = $data['nombre'];
        $tipoVendedor->porcentaje_com_factura = $data['porcentaje_factura'];
        $tipoVendedor->porcentaje_comisiones = $data['porcentaje_comisiones'];
        $tipoVendedor->save();

        return $tipoVendedor;
    }

    /**
     * Actualizar un registro existente por ID
     */
    public static function actualizar(int $id, array $data)
    {
        $tipoVendedor = self::find($id);

        if ($tipoVendedor) {
            $tipoVendedor->nombre = $data['nombre'];
            $tipoVendedor->porcentaje_com_factura = $data['porcentaje_factura'];
            $tipoVendedor->porcentaje_comisiones = $data['porcentaje_comisiones'];
            $tipoVendedor->save();
        }

        return $tipoVendedor;
    }

    public static function borrar(int $id)
    {
        $tipoVendedor = self::find($id);

        if ($tipoVendedor) {
            $tipoVendedor->activo = 0;
            $tipoVendedor->save();
        }

        return $tipoVendedor;
    }
}
