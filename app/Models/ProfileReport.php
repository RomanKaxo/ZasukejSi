<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileReport extends Model
{
    protected $fillable = [
        'profile_id',
        'email',
        'message',
        'screenshot_path',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
