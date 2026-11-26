<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocumentSigner extends Model
{
    protected $fillable = [
        'signing_request_id',
        'user_id',
        'signer_role',
        'signing_order',
        'status',
        'signed_at',
        'rejected_at',
        'remarks',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'rejected_at' => 'datetime',
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

    public function signature(): HasOne
    {
        return $this->hasOne(DocumentSignature::class);
    }
}
