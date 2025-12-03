<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\AccessLikeFactory;

class AccessLike extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = [
        'user_id',
        'level_name',
        'response',
        'system_type',
    ];

    protected $table = 'com_access_like';

    public function scopeCompras ($query) {
        return $query->where('system_type', 1);
    }

    public function scopeMcro ($query) {
        return $query->where('system_type', 2);
    }

    protected static function newFactory(): AccessLikeFactory
    {
        //return AccessLikeFactory::new();
    }
}
