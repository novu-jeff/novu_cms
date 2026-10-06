<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberDocument extends Model
{
    protected $connection = 'lis_mysql';

    protected $table = 'member_documents';

    protected $fillable = [
        'member_id',
        'member_account_id',
        'title',
        'description',
        'file_name',
        'file_path',
        'status',
        'remarks',
        'session_id',
        'agenda_order'
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function memberAccount()
    {
        return $this->belongsTo(MembersAccount::class, 'member_account_id');
    }

    public function sessionDocument()
    {
        return $this->hasOne(SessionDocument::class, 'document_id');
    }

    public function documentRemarks()
    {
       /* return $this->hasMany(DocumentRemark::class,'document_id')
            ->whereNull('parent_id')   // main remarks only
            ->latest();*/

        return $this->hasMany(DocumentRemark::class,'document_id');    
    }
}
