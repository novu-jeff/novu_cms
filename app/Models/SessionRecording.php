<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SessionRecording extends Model
{
    protected $fillable = [
        'session_id',
        'uploaded_by',
        'original_filename',
        'disk',
        'file_path',
        'mime_type',
        'file_size',
        'status',
        'error_message',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SessionMeeting::class, 'session_id');
    }

    public function transcript(): HasOne
    {
        return $this->hasOne(SessionTranscript::class, 'recording_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}