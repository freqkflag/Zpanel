<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class APIService extends Model
{
    use HasFactory;

    protected $fillable = [
        'kong_service_id',
        'name',
        'url',
        'routes',
        'plugins',
        'status',
    ];

    protected $casts = [
        'routes' => 'array',
        'plugins' => 'array',
    ];
}
