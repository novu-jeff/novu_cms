<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommitteeMeeting extends Model
{
    protected $connection = 'mysql';

    protected $table = 'committee_meetings';

    protected $fillable = [
        'committee_id',
        'title',
        'meeting_date',
        'start_time',
        'end_time',
        'venue',
        'status',
        'presiding_officer',
        'quorum_required',
        'current_agenda_item_id',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'quorum_required' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class, 'committee_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(
            CommitteeMeetingAttendance::class,
            'committee_meeting_id'
        );
    }

    public function agendaItems(): HasMany
    {
        return $this->hasMany(
            CommitteeMeetingAgendaItem::class,
            'committee_meeting_id'
        )->orderBy('agenda_order');
    }

    public function report(): HasOne
    {
        return $this->hasOne(
            CommitteeMeetingReport::class,
            'committee_meeting_id'
        );
    }
}