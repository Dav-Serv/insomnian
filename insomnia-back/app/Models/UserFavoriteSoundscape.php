<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SoundScapes extends Model
{
    protected $table = 'user_favorite_soundscapes';

    protected $fillable = [
        'title',
        'artist_name',
        'description',
        'category',
        'duration_minutes',
        'thumbnail_url',
        'audio_url'
    ];

    /**
     * Relasi many-to-many dengan User (favorit)
     */
    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_favorite_soundscapes',
            'soundscape_id',
            'user_id'
        )->withTimestamps();
    }
}