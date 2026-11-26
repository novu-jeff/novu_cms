<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionMinuteReview extends Model
{
    protected $fillable = [
        'session_id',
        'reviewer_id',
        'action',
        'comments',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(
            SessionMeeting::class,
            'session_id'
        );
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
