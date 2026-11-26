<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class SessionVoteRecord extends Model
{
    protected $connection = 'mysql';
    protected $table = 'session_vote_records';

    protected $fillable = [
        'session_vote_id',
        'member_account_id',
        'vote',
        'cast_at',
    ];

    protected $casts = [
        'cast_at' => 'datetime',
    ];

    public function votingEvent(): BelongsTo
    {
        return $this->belongsTo(
            SessionVote::class,
            'session_vote_id'
        );
    }

    public function memberAccount(): BelongsTo
    {
        return $this->belongsTo(
            MembersAccount::class,
            'member_account_id'
        );
    }
}
