<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionDocument extends Model
{
    protected $connection = 'mysql';
    protected $table = 'session_documents';
    protected $fillable = [
        'session_id',
        'document_id',
        'agenda_order'
    ];

     public function document()
    {
        return $this->belongsTo(MemberDocument::class, 'document_id');
    }

    public function session()
    {
        return $this->belongsTo(SessionMeeting::class);
    }

   
}
