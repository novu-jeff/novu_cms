<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CommitteeReferral extends Model
{
    protected $connection = 'mysql';

    protected $table = 'committee_referrals';

    protected $fillable = [
        'session_document_id',
        'committee_id',
        'referral_remarks',
        'status',
        'referred_by',
        'referred_at',
        'recommendation',
        'report_file_path',
        'report_submitted_at',
    ];

    protected $casts = [
        'referred_at' => 'datetime',
        'report_submitted_at' => 'datetime',
    ];

    public function sessionDocument(): BelongsTo
    {
        return $this->belongsTo(
            SessionDocument::class,
            'session_document_id'
        );
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(
            Committee::class,
            'committee_id'
        );
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'referred_by'
        );
    }

    public function meetingReports(): BelongsToMany
    {
        return $this->belongsToMany(
            CommitteeMeetingReport::class,
            'committee_meeting_report_referrals',
            'committee_referral_id',
            'committee_meeting_report_id'
        )->withTimestamps();
    }
}
