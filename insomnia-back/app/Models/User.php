<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'photo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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

    // Relasi ke Soundscape
    public function FavoriteSoundscapes() : BelongsToMany {
        return $this->belongsToMany(
            SoundScapes::class,
            'user_favorite_soundscapes',
            'user_id',
            'soundscape_id'
        )->withTimestamps();
    }

    // Relasi ke catatan tidur (Diary)
    public function sleepLogs(): HasMany {
        return $this->hasMany(SleepLogs::class, 'user_id');
    }

    // Relasi ke analitik tidur harian
    public function dailySleepAnalytics(): HasMany {
        return $this->hasMany(DailySleepAnalytics::class, 'user_id');
    }
}
