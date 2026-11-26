<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationSetting extends Model
{
    protected $fillable = [
        'organization_name',
        'organization_subtitle',
        'logo_path',
        'address',
        'contact_information',
    ];
}