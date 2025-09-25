<?php

namespace App\Models\Sanctum;

// namespace App\Sanctum;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends SanctumToken
{
    protected $connection = 'intranet';
}
