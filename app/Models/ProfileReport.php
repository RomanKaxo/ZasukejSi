<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileReport extends Model
{
    protected $fillable = [
        'profile_id',
        'reporter_id',
        'email',
        'message',
        'screenshot_path',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /** Present submitted reports through the existing member case cards. */
    public function getReasonAttribute(): string
    {
        return $this->message;
    }

    public function getAllegationsAttribute(): array
    {
        return [];
    }
}
