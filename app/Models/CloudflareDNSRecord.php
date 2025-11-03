<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CloudflareDNSRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_id',
        'zone_id',
        'type',
        'name',
        'content',
        'ttl',
        'proxied',
        'priority',
    ];

    protected $casts = [
        'proxied' => 'boolean',
        'ttl' => 'integer',
        'priority' => 'integer',
    ];

    /**
     * Get the zone that owns the DNS record
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(CloudflareZone::class, 'zone_id', 'zone_id');
    }
}
