<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionPreviousMinutesAgendaItem extends Model
{
    use HasFactory;

    protected $table = 'session_previous_minutes_agenda_items';

    protected $fillable = [
        'session_id',
        'previous_session_id',
        'agenda_order',
        'status',
        'decision',
        'motion_moved_by',
        'motion_seconded_by',
        'remarks',
        'decision_recorded_by',
        'decision_recorded_at',
    ];

    protected $casts = [
        'agenda_order' => 'integer',
        'decision_recorded_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(
            SessionMeeting::class,
            'session_id'
        );
    }

    public function previousSession(): BelongsTo
    {
        return $this->belongsTo(
            SessionMeeting::class,
            'previous_session_id'
        );
    }

    public function decisionRecordedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'decision_recorded_by'
        );
    }
}