<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SoundscapeResource;
use App\Models\SoundScapes;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SoundScapeController extends Controller
{
    /**
     * Daftar soundscape dengan filter kategori
     * GET /api/soundscapes?category=nature
     */
    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');

        $query = SoundScapes::query();

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $soundscapes = $query->get();

        return response()->json([
            'success' => true,
            'data'    => SoundscapeResource::collection($soundscapes)
        ]);
    }

    /**
     * Detail soundscape (termasuk audio_url)
     * GET /api/soundscapes/{id}
     */
    public function show(Request $request,int $id): JsonResponse
    {
        $soundscape = SoundScapes::find($id);

        if (!$soundscape) {
            return response()->json([
                'success' => false,
                'message' => 'Soundscape not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new SoundscapeResource($soundscape)
        ]);
    }

    /**
     * Toggle favorite (tambah/hapus)
     * POST /api/soundscapes/{id}/favorite
     */
    public function toggleFavorite(Request $request,int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $soundscape = SoundScapes::find($id);

        if (!$soundscape) {
            return response()->json([
                'success' => false,
                'message' => 'Soundscape not found'
            ], 404);
        }

        $isFavorited = $user->favoriteSoundscapes()->where('soundscape_id', $id)->exists();

        if ($isFavorited) {
            $user->favoriteSoundscapes()->detach($id);
            $message = 'Removed from favorites';
        } else {
            $user->favoriteSoundscapes()->attach($id);
            $message = 'Added to favorites';
        }

        return response()->json([
            'success'      => true,
            'message'      => $message,
            'is_favorited' => !$isFavorited,
        ]);
    }

    /**
     * Daftar favorit user
     * GET /api/favorites
     */
    public function favorites(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $favorites = $user->favoriteSoundscapes;

        return response()->json([
            'success' => true,
            'data'    => SoundscapeResource::collection($favorites)
        ]);
    }
    public function updateAudioUrl(Request $request,int $id): JsonResponse
{
    $soundscape = SoundScapes::find($id);

    if (!$soundscape) {
        return response()->json([
            'success' => false,
            'message' => 'Soundscape not found'
        ], 404);
    }

    $request->validate([
        'audio_url' => 'required|url'
    ]);

    $soundscape->audio_url = $request->audio_url;
    $soundscape->save();

    return response()->json([
        'success' => true,
        'message' => 'Audio URL updated successfully',
        'data'    => new SoundscapeResource($soundscape)
    ]);
}
}