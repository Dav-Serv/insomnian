<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SoundscapeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'artist_name'      => $this->artist_name,
            'description'      => $this->description,
            'category'         => $this->category,
            'duration_minutes' => $this->duration_minutes,
            'thumbnail_url'    => $this->thumbnail_url ? (filter_var($this->thumbnail_url, FILTER_VALIDATE_URL) ? $this->thumbnail_url : asset($this->thumbnail_url)) : null,
            'audio_url'        => $this->audio_url ? (filter_var($this->audio_url, FILTER_VALIDATE_URL) ? $this->audio_url : asset($this->audio_url)) : null,
            'is_favorited'     => $this->when($user, function () use ($user) {
                return $this->favoritedByUsers()->where('user_id', $user->id)->exists();
            }),
            'created_at'       => $this->created_at?->toISOString(),
        ];
    }
}