<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MembersAccount;

class Member extends Model
{
    protected $connection = 'mysql';

    protected $table = 'members';

    protected $guarded = ['id', 'created_at', 'updated_at'];

        protected $fillable = [
            'name',
            'position',
            'description',
            'image_path',
            'email',
            'contact_number',
            'address',
            'term_start',
            'term_end',
            'achievements',
            'priority_projects',
            'social_facebook',
            'social_twitter',
            'social_instagram',
            'isActive',
            'sort_order',
        ];

        public function account()
        {
            return $this->hasOne(MembersAccount::class, 'member_id');
        }
}
