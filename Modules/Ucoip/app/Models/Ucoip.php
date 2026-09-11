<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Ucoip\Database\Factories\UcoipFactory;

class Ucoip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ucoip',
        'contrasenia',
        'usuario_as',
        'ip',
        'extension',
        'movil',
        'activo',
        'user_id',
        'ucoip_cat_puestos',
        'cat_empresa_id'
        ];

        protected $table = 'ucoip_ucoip';

    protected static function newFactory(): UcoipFactory
    {
        //return UcoipFactory::new();
    }

    public function userGlpi(){
       return $this->belongsTo(GlpiUser::class, 'user_id',  'id')->select('id', 'firstname', 'realname',  'name');
    }

    public function puesto(){
        return $this->belongsTo(CatPuestos::class, 'ucoip_cat_puestos', 'id');
    }

    public function extensiones()
    {
        return $this->hasMany(RecursosRedUcoip::class,'ucoip_ucoip_id', 'id' )->where('ucoip_cat_recursos_id', '4');
    }

    public function activos(){
        return $this->hasMany(HardwareUcoip::class,'ucoip_ucoip_id', 'id')->whereNull('fecha_fin');
    }

    public function sistemas(){
        return $this->hasMany(SistemasUcoip::class,'ucoip_ucoip_id', 'id')->whereNull('fecha_fin');
    }

    public function recursosRed()
    {
        return $this->hasMany(RecursosRedUcoip::class,'ucoip_ucoip_id', 'id' )->whereNull('fecha_retiro');
    }

    public function licenciamientos()
    {
        return $this->hasMany(SoftwareUcoip::class,'ucoip_ucoip_id', 'id' )->whereNull('fecha_retiro');
    }

    public function tokens()
    {
        return $this->hasMany(TokensUcoip::class,'ucoip_ucoip_id', 'id' )->whereNull('fecha_retiro');
    }

    public function empresa(){
        return $this->belongsTo(CatEmpresas::class,'cat_empresa_id', 'id' );
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class,'ucoip_ucoip_id', 'id');
    }

    /**Un hgardware tien una asignacion actual */
    public function titularActual()
    {
        return $this->hasOne(TitularesUcoip::class, 'ucoip_ucoip_id', 'id')
                    ->whereNull('fecha_fin')
                    ->latestOfMany();
    }

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
}
