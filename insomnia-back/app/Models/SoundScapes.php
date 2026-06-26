<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoundScapes extends Model
{
    protected $fillable = [
        'title',
        'artist_name',
        'description',
        'category',
        'duration_minutes',
        'thumbnail_url',
        'audio_url'
    ];
}
