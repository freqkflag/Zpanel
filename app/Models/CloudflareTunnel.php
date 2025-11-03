<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CloudflareTunnel extends Model
{
    use HasFactory;

    protected $fillable = [
        'tunnel_id',
        'account_id',
        'name',
        'secret',
        'status',
        'connections',
    ];

    protected $casts = [
        'connections' => 'array',
    ];
}
