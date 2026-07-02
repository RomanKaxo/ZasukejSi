<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'password',
    ];

    /**
     * Check if the user is female.
     */
    public function isFemale(): bool
    {
        return $this->gender === 'female';
    }

    /**
     * Check if the user is male.
     */
    public function isMale(): bool
    {
        return $this->gender === 'male';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get the user's notifications.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get messages sent by this user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'from_user_id');
    }

    /**
     * Get messages received by this user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'to_user_id');
    }

    /**
     * Get profiles this user has favorited.
     */
    public function favoriteProfiles()
    {
        return $this->belongsToMany(Profile::class, 'profile_favorites')
            ->withTimestamps();
    }

    /**
     * Check if the user has favorited a profile.
     */
    public function hasFavorited(Profile $profile): bool
    {
        return $this->favoriteProfiles()->where('profile_id', $profile->id)->exists();
    }

    /**
     * Toggle favorite status for a profile.
     */
    public function toggleFavorite(Profile $profile): bool
    {
        if ($this->hasFavorited($profile)) {
            $this->favoriteProfiles()->detach($profile->id);
            return false;
        }

        $this->favoriteProfiles()->attach($profile->id);
        return true;
    }

    /**
     * Check if the user has VIP access to view VIP profiles.
     */
    public function hasVipAccess(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('vip');
    }

    /**
     * Determine if the user can access the Filament admin panel.
     * Only users with the 'admin' role can access the panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin');
    }
}
