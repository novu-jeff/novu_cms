<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlbumPhoto extends Model
{
    protected $table = 'album_photo';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
