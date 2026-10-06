<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRemark extends Model
{
    protected $connection = 'lis_mysql';
    protected $table = 'document_remarks';

    protected $fillable = [
        'document_id',
        'parent_id',
        'member_account_id',
        'remark'
    ];

    public function document()
    {
        return $this->belongsTo(MemberDocument::class,'document_id');
    }

    public function member()
    {
        return $this->belongsTo(MembersAccount::class,'member_account_id');
    }

    public function replies()
    {
        return $this->hasMany(DocumentRemark::class,'parent_id')
            ->with('member.member');
    }

    public function parent()
    {
        return $this->belongsTo(DocumentRemark::class,'parent_id');
    }

}
