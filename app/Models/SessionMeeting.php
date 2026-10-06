<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionMeeting extends Model
{
    protected $fillable = [
        'title',
        'session_date',
        'status',
        'packet_file',
        'call_to_order',
        'prayer',
        'roll_call',
        'adjournment_time',
    ];

    public function documents()
    {
        return $this->hasMany(SessionDocument::class, 'session_id');
    }
}
