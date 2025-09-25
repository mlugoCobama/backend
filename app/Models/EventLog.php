<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'operacion',
        'endpoint',
        'direccion_ip'
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'event_logs';
    protected $connection = 'dashboard';

}
