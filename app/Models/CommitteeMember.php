<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    protected $table  = 'standing_comittee_member';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
