<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommitteeMeetingAgendaItem extends Model
{
    protected $connection = 'mysql';

    protected $table = 'committee_meeting_agenda_items';

    protected $fillable = [
        'committee_meeting_id',
        'committee_referral_id',
        'agenda_order',
        'status',
    ];

    protected $casts = [
        'agenda_order' => 'integer',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(
            CommitteeMeeting::class,
            'committee_meeting_id'
        );
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(
            CommitteeReferral::class,
            'committee_referral_id'
        );
    }

    public function actions(): HasMany
    {
        return $this->hasMany(
            CommitteeMeetingAgendaAction::class,
            'committee_meeting_agenda_item_id'
        );
    }
}