<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

class MembersAccount extends Authenticatable
{
    protected $connection = 'mysql';
    protected $table = 'members_accounts';

    protected $fillable = [
        'member_id',
        'email',
        'password',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
