<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SoundscapeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        // Resolusi thumbnail dengan fallback CDN jika berkas gambar lokal tidak ditemukan
        $thumbnail = $this->thumbnail_url;
        if ($thumbnail && !filter_var($thumbnail, FILTER_VALIDATE_URL)) {
            if (!file_exists(public_path($thumbnail))) {
                $category = strtolower($this->category);
                $thumbnail = match ($category) {
                    'rain' => 'https://images.unsplash.com/photo-1534274988757-a28bf1a57c17?w=400&q=80',
                    'nature' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400&q=80',
                    'binaural' => 'https://images.unsplash.com/photo-1518241353330-0f7941c2d9b5?w=400&q=80',
                    default => 'https://images.unsplash.com/photo-1518241353330-0f7941c2d9b5?w=400&q=80',
                };
            } else {
                $thumbnail = asset($thumbnail);
            }
        }

        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'artist_name'      => $this->artist_name,
            'description'      => $this->description,
            'category'         => $this->category,
            'duration_minutes' => $this->duration_minutes,
            'thumbnail_url'    => $thumbnail,
            'audio_url'        => $this->audio_url ? (filter_var($this->audio_url, FILTER_VALIDATE_URL) ? $this->audio_url : asset($this->audio_url)) : null,
            'is_favorited'     => $this->when($user, function () use ($user, $request) {
                // Cache daftar ID favorit user di request untuk menghindari N+1 database queries
                if (!$request->has('__favorite_soundscape_ids')) {
                    $ids = $user->FavoriteSoundscapes()->pluck('soundscape_id')->toArray();
                    $request->merge(['__favorite_soundscape_ids' => $ids]);
                }
                $favorites = $request->input('__favorite_soundscape_ids');
                return in_array($this->id, $favorites);
            }),
            'created_at'       => $this->created_at?->toISOString(),
        ];
    }
}