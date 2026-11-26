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
        'motion_moved_by',
        'motion_seconded_by',
        'official_reference',
        'decision',
    ];
}
