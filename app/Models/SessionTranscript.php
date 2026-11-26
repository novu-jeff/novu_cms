<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionTranscript extends Model
{
    protected $fillable = [
        'recording_id',
        'transcript_text',
        'language',
        'provider',
        'provider_metadata',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'provider_metadata' => 'array',
        ];
    }

    public function recording(): BelongsTo
    {
        return $this->belongsTo(
            SessionRecording::class,
            'recording_id'
        );
    }
}