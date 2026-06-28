<?php

namespace Database\Seeders;

use App\Models\SoundScapes;
use Illuminate\Database\Seeder;

class SoundScapeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'title'            => 'Rain in Kyoto',
                'artist_name'      => 'Samael Sound',
                'description'      => 'Suara rintik hujan menenangkan dari kuil Kyoto untuk tidur nyenyak.',
                'category'         => 'Rain',
                'duration_minutes' => 15,
                'thumbnail_url'    => 'images/rain.jpg',
                'audio_url'        => 'sounds/rain_kyoto.mp3',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'title'            => 'Deep Forest Birds',
                'artist_name'      => 'Nature Rec',
                'description'      => 'Suara alam liar dan kicauan burung malam hari di dalam hutan asri.',
                'category'         => 'Nature',
                'duration_minutes' => 20,
                'thumbnail_url'    => 'images/forest.jpg',
                'audio_url'        => 'sounds/forest.mp3',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'title'            => 'Binaural Focus 40Hz',
                'artist_name'      => 'Wave Therapy',
                'description'      => 'Gelombang binaural beats untuk merelaksasi gelombang otak sebelum tidur.',
                'category'         => 'Binaural',
                'duration_minutes' => 30,
                'thumbnail_url'    => 'images/binaural.jpg',
                'audio_url'        => 'sounds/binaural.mp3',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        ];

        SoundScapes::insert($data);
    }
}