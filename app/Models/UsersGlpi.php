<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersGlpi extends Model
{
    use HasFactory;

    protected $connection = 'intranet';

    protected $table = 'glpi_users';
}
