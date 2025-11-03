<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CloudflareZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'name',
        'account_id',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];
}
