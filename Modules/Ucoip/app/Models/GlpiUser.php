<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\GlpiUserFactory;

class GlpiUser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'name',
        'password',
        'password_last_update',
        'phone',
        'phone2',
        'mobile',
        'realname',
        'firstname',
        'locations_id',
        'is_active', 
        'comment',
        'entities_id', 
        'intercompania', 
        'dominio', 
        'fecha_nacimiento', 
        'id_areas_directorio', 
        'id_puesto_directorio', 
        'id_departamentos_directorio',
        'ipl'
    ];

    protected $connection =  'intranet'; 
    protected $table = 'glpi_users';

    protected static function newFactory(): GlpiUserFactory
    {
        //return GlpiUserFactory::new();
    }
}
