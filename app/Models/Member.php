<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
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
}
