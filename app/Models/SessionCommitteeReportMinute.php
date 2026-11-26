<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionCommitteeReportMinute extends Model
{
    protected $connection = 'mysql';

    protected $table = 'session_committee_report_minutes';

    protected $fillable = [
        'session_id',
        'committee_report_agenda_item_id',
        'discussion',
        'motion',
        'motion_moved_by',
        'motion_seconded_by',
        'official_reference',
        'decision',
        'prepared_by',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SessionMeeting::class, 'session_id');
    }

    public function agendaItem(): BelongsTo
    {
        return $this->belongsTo(
            CommitteeReportAgendaItem::class,
            'committee_report_agenda_item_id'
        );
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }
}