<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SoundScapes extends Model
{
    protected $table = 'sound_scapes';

    protected $fillable = [
        'title',
        'artist_name',
        'description',
        'category',
        'duration_minutes',
        'thumbnail_url',
        'audio_url'
    ];

    public function FavoritedByUsers() : BelongsToMany {
        return $this->belongsToMany(
            User::class,
            'user_favorite_soundscapes',
            'soundscape_id',
            'user_id'
        )->withTimestamps();
    }
}
