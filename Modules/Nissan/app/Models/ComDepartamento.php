<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\ComDepartamentoFactory;

class ComDepartamento extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'activo'
    ];

    protected $table = 'com_departamentos';

    protected $connection = 'autos';


    public function vendedores()
    {
        return $this->hasMany(Vendedor::class, 'com_departamentos_id');
    }

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }

    protected static function newFactory(): ComDepartamentoFactory
    {
        //return ComDepartamentoFactory::new();
    }

        /**
     * Crear un nuevo registro
     */
    public static function crear(array $data)
    {
        $departamento = new self();
        $departamento->nombre = $data['nombre'];
        $departamento->save();

        return $departamento;
    }

    /**
     * Actualizar un registro existente por ID
     */
    public static function actualizar(int $id, array $data)
    {
        $departamento = self::find($id);

        if ($departamento) {
            $departamento->nombre = $data['nombre'];
            $departamento->save();
        }

        return $departamento;
    }

    public static function borrar(int $id)
    {
        $departamento = self::find($id);

        if ($departamento) {
            $departamento->activo = 0;
            $departamento->save();
        }

        return $departamento;
    }
}
