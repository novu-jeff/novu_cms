<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangayOfficial extends Model
{
    use HasFactory;

    protected $appends = ['barangay_name'];

    protected $fillable = [
        'name',
        'position',
        'image_path',
        'isActive',
        'barangay_id'
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function getBarangayNameAttribute()
    {   
        return $this->barangay->name ?? '—';
    }
}
