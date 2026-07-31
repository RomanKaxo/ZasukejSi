<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    /** @use HasFactory<\Database\Factories\ReportFactory> */
    use HasFactory;

    public const ALLEGATION_CATEGORIES = [
        'theft',
        'photo_mismatch',
        'fraud',
        'threats',
        'fake_profile',
        'inappropriate_behavior',
    ];

    protected $fillable = [
        'profile_id',
        'reporter_id',
        'reason',
        'allegations',
        'blocked_at',
    ];

    protected function casts(): array
    {
        return [
            'allegations' => 'array',
            'blocked_at' => 'datetime',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
