<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    protected $table = 'standing_comittee';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function committeeMembers()
    {
        return $this->hasMany(
            CommitteeMember::class,
            'standing_comittee_id'
        );
    }

    public function activeMembers()
    {
        return $this->committeeMembers()
            ->where('isActive', true);
    }

    public function chairperson()
    {
        return $this->hasOne(
            CommitteeMember::class,
            'standing_comittee_id'
        )
        ->where('isActive', true)
        ->where('is_chairperson', true);
    }
}
