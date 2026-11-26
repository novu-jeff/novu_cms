<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionMeeting extends Model
{
    protected $fillable = [
        // Existing session fields
        'title',
        'session_date',
        'status',
        'packet_file',
        'call_to_order',
        'prayer',
        'roll_call',
        'adjournment_time',
        'adjournment_reason',
        'adjournment_by',
        'adjournment_to',

        // Minutes documentation fields
        'quorum_confirmation',
        'previous_minutes_status',

        // Session scheduling and management fields
        'session_type',
        'start_time',
        'end_time',
        'venue',
        'session_number',
        'legislative_term',
        'presiding_officer',
        'quorum_required',
        'current_agenda_item_id',
        'current_agenda_item_type',
        'started_at',
        'ended_at',

        // Session minute review workflow fields
        'minutes_status',
        'minutes_submitted_by',
        'minutes_submitted_at',
        'minutes_reviewed_by',
        'minutes_reviewed_at',
        'minutes_pdf_path'
    ];

    protected $casts = [
        'session_date' => 'date',
        'quorum_required' => 'integer',
        'minutes_finalized_at' => 'datetime',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(SessionDocument::class, 'session_id');
    }

    public function committeeReportAgendaItems(): HasMany
    {
        return $this->hasMany(
            CommitteeReportAgendaItem::class,
            'session_id'
        );
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(SessionAttendance::class, 'session_id');
    }

    public function minuteReviews(): HasMany
    {
        return $this->hasMany(SessionMinuteReview::class, 'session_id');
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(SessionRecording::class, 'session_id');
    }

    public function suspensions(): HasMany
    {
        return $this->hasMany(SessionSuspension::class, 'session_id');
    }

    public function proceedings(): HasMany
    {
        return $this->hasMany(SessionProceeding::class, 'session_id')
            ->orderBy('created_at');
    }

    public function previousMinutesAgendaItems()
    {
        return $this->hasMany(
            SessionPreviousMinutesAgendaItem::class,
            'session_id'
        );
    }
}
