<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    protected $table  = 'standing_comittee';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
