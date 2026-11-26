<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    protected $table = 'standing_comittee_member';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'isActive' => 'boolean',
        'term_start' => 'date',
        'term_end' => 'date',
        'is_chairperson' => 'boolean',
    ];

    public function committee()
    {
        return $this->belongsTo(
            Committee::class,
            'standing_comittee_id'
        );
    }

    public function member()
    {
        return $this->belongsTo(
            Member::class,
            'member_id'
        );
    }

    public function scopeActive($query)
    {
        return $query->where('isActive', true);
    }

    public function scopeChairperson($query)
    {
        return $query->where('is_chairperson', true);
    }
}