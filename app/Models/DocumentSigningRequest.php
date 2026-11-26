<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentSigningRequest extends Model
{
    protected $fillable = [
        'document_type',
        'document_id',
        'committee_id',
        'reference_no',
        'document_title',
        'document_path',
        'document_hash',
        'signed_document_path',
        'signed_document_hash',
        'status',
        'initiated_by',
        'submitted_at',
        'completed_at',
        'revoked_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function signers(): HasMany
    {
        return $this->hasMany(DocumentSigner::class, 'signing_request_id')
            ->orderBy('signing_order');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(DocumentSigningAudit::class, 'signing_request_id')
            ->orderBy('created_at');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'fully_signed';
    }
}
