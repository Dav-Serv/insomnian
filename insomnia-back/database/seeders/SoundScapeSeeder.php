<?php

namespace Database\Seeders;

use App\Models\SoundScapes;
use Illuminate\Database\Seeder;

class SoundScapeSeeder extends Seeder
{
    public function run(): void
    {
        $soundscapes = [
            [
                'title' => 'Ubud Forest Canopy',
                'artist_name' => 'Nature Sounds',
                'description' => 'Gentle rustling leaves and distant nocturnal wildlife...',
                'category' => 'nature',
                'duration_minutes' => 45,
                'thumbnail_url' => 'https://images.unsplash.com/photo-1545569341-9eb8b30979d9?w=400',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
            ],
            // tambahkan lainnya...
        ];

        foreach ($soundscapes as $soundscape) {
            SoundScapes::create($soundscape);
        }
    }
}