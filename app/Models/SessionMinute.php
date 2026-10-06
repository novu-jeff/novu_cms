<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionMinute extends Model
{
    protected $fillable = [
        'session_id',
        'document_id',
        'discussion',
        'motion',
        'decision'
    ];
}
