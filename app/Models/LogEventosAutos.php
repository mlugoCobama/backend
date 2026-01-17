<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogEventosAutos extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'table_name',
        'record_id',
        'event',
        'old_values',
        'new_values',
        'ip_address',
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'log_eventos';
    protected $connection = 'autos';

    // protected $cast = [
    //     'old_values' => 'array',
    //     'new_values' => 'array',

    // ];

    public function setOldValuesAttribute($value)
    {
        $this->attributes['old_values'] = is_array($value) ? json_encode($value) : $value;
    }

    public function setNewValuesAttribute($value)
    {
        $this->attributes['new_values'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getOldValuesAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getNewValuesAttribute($value)
    {
        return json_decode($value, true);
    }
}
