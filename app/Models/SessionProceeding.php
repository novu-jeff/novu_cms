<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionProceeding extends Model
{
    protected $connection = 'mysql';

    protected $table = 'session_proceedings';

    protected $fillable = [
        'session_id',
        'agenda_item_id',
        'agenda_type',
        'entry_type',
        'content',
        'moved_by',
        'seconded_by',
        'recorded_by',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SessionMeeting::class, 'session_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
