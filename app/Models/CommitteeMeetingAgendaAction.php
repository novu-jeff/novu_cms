<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeMeetingAgendaAction extends Model
{
    protected $connection = 'mysql';

    protected $table = 'committee_meeting_agenda_actions';

    protected $fillable = [
        'committee_meeting_id',
        'committee_meeting_agenda_item_id',
        'action',
        'reason',
        'recommendation',
        'acted_by',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(
            CommitteeMeeting::class,
            'committee_meeting_id'
        );
    }

    public function agendaItem(): BelongsTo
    {
        return $this->belongsTo(
            CommitteeMeetingAgendaItem::class,
            'committee_meeting_agenda_item_id'
        );
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}