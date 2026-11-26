<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionVote extends Model
{
    protected $connection = 'mysql';
    protected $table = 'session_votes';

    protected $fillable = [
        'session_id',
        'session_document_id',
        'committee_report_agenda_item_id',
        'motion_title',
        'motion_description',
        'voting_method',
        'status',
        'opened_by',
        'opened_at',
        'closed_by',
        'closed_at',
        'result',
        'finalized_by',
        'finalized_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'finalized_at' => 'datetime',
        
    ];

    public function eligibilities(): HasMany
    {
        return $this->hasMany(
            SessionVoteEligibility::class,
            'session_vote_id'
        );
    }

    public function records(): HasMany
    {
        return $this->hasMany(
            SessionVoteRecord::class,
            'session_vote_id'
        );
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(SessionMeeting::class, 'session_id');
    }

    public function sessionDocument(): BelongsTo
    {
        return $this->belongsTo(SessionDocument::class, 'session_document_id');
    }

    public function committeeReportAgendaItem(): BelongsTo
    {
        return $this->belongsTo(
            CommitteeReportAgendaItem::class,
            'committee_report_agenda_item_id'
        );
    }
}