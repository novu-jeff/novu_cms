<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CommitteeMeetingReport extends Model
{
    protected $table = 'committee_meeting_reports';

    protected $fillable = [
        'committee_meeting_id',
        'file_path',
        'original_filename',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(
            CommitteeMeeting::class,
            'committee_meeting_id'
        );
    }

    public function referrals(): BelongsToMany
    {
        return $this->belongsToMany(
            CommitteeReferral::class,
            'committee_meeting_report_referrals',
            'committee_meeting_report_id',
            'committee_referral_id'
        )->withTimestamps();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}