<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'organization_node';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
