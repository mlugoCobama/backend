<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\AuditoriaFactory;
use Modules\Ucoip\Services\GlpiService;

class Auditoria extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'ucoip_auditorias';

    protected $fillable = [
        'ucoip_ucoip_id',
        'responsable_id',
        'fecha',
        'observaciones',
        'active'

    ];



    protected static function newFactory(): AuditoriaFactory
    {
        //return AuditoriaFactory::new();
    }

     protected $casts = [
        'fecha' => 'datetime',
    ];

    public function ucoip()
    {
        return $this->belongsTo(Ucoip::class, 'ucoip_ucoip_id');
    }

    public function responsable()
    {
        return $this->belongsTo(GlpiUser::class, 'responsable_id')->select('id', 'firstname', 'realname',  'name');
    }

    public function detalles()
    {
        return $this->hasMany(
            AuditoriaDetalle::class,
            'ucoip_auditorias_id'
        );
    }

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
}
