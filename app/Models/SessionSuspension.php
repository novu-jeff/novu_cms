<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionSuspension extends Model
{
    protected $fillable = [
        'session_id',
        'reason',
        'suspended_by',
        'resumed_by',
        'suspended_at',
        'resumed_at',
    ];

    protected $casts = [
        'suspended_at' => 'datetime',
        'resumed_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SessionMeeting::class, 'session_id');
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function resumedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resumed_by');
    }
}
