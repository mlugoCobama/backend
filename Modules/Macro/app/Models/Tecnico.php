<?php

namespace Modules\Macro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Macro\Database\Factories\TecnicoFactory;

class Tecnico extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        '',
        '',
        '',
        '',
        '',

    ];

    protected static function newFactory(): TecnicoFactory
    {
        //return TecnicoFactory::new();
    }
}
