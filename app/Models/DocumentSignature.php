<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSignature extends Model
{
    protected $fillable = [
        'document_signer_id',
        'signature_type',
        'signature_path',
        'document_hash',
        'signature_hash',
        'ip_address',
        'user_agent',
        'certificate_serial',
        'certificate_issuer',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function signer(): BelongsTo
    {
        return $this->belongsTo(
            DocumentSigner::class,
            'document_signer_id'
        );
    }
}
