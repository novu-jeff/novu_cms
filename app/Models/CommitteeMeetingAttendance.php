<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeMeetingAttendance extends Model
{
    protected $connection = 'mysql';

    protected $table = 'committee_meeting_attendances';

    protected $fillable = [
        'committee_meeting_id',
        'committee_member_id',
        'attendee_name',
        'attendee_role',
        'attendance_status',
        'remarks',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(
            CommitteeMeeting::class,
            'committee_meeting_id'
        );
    }

    public function committeeMember(): BelongsTo
    {
        return $this->belongsTo(
            CommitteeMember::class,
            'committee_member_id'
        );
    }
}