<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionVoteEligibility extends Model
{
    protected $connection = 'mysql';
    protected $table = 'session_vote_eligibilities';

    protected $fillable = [
        'session_vote_id',
        'member_account_id',
        'eligible',
        'eligibility_reason',
    ];

    protected $casts = [
        'eligible' => 'boolean',
    ];

    public function vote(): BelongsTo
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
