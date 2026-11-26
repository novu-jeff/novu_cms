<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionAgendaAction extends Model
{
    protected $connection = 'mysql';

    protected $table = 'session_agenda_actions';

    protected $fillable = [
        'session_id',
        'session_document_id',
        'committee_report_agenda_item_id',
        'action',
        'reason',
        'referred_to',
        'acted_by',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

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

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    // Retained for existing document-based action history.
    public function agendaItem(): BelongsTo
    {
        return $this->belongsTo(SessionDocument::class, 'session_document_id');
    }
}