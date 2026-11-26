<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSigningAudit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'signing_request_id',
        'user_id',
        'event',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function signingRequest(): BelongsTo
    {
        return $this->belongsTo(
            DocumentSigningRequest::class,
            'signing_request_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
