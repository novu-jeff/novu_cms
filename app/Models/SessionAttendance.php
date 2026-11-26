<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionAttendance extends Model
{
    protected $table = 'session_attendances';

    protected $fillable = [
        'session_id',
        'member_account_id',
        'attendee_name',
        'attendee_role',
        'attendance_status',
        'remarks',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Keep attendance rows on the same database as SessionMeeting.
        $connection = (new SessionMeeting())->getConnectionName();
        if ($connection) {
            $this->setConnection($connection);
        }
    }

    public function session()
    {
        return $this->belongsTo(SessionMeeting::class, 'session_id');
    }

    public function memberAccount()
    {
        return $this->belongsTo(
            MembersAccount::class,
            'member_account_id'
        );
    }
}
