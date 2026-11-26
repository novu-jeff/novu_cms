<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeReportAgendaItem extends Model
{
    protected $connection = 'mysql';

    protected $table = 'committee_report_agenda_items';

    protected $fillable = [
        'committee_referral_id',
        'session_id',
        'agenda_order',
        'status',
    ];

    public function referral(): BelongsTo
    {
        return $this->belongsTo(
            CommitteeReferral::class,
            'committee_referral_id'
        );
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(
            SessionMeeting::class,
            'session_id'
        );
    }
}
