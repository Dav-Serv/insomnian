<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SoundscapeResource;
use App\Models\SoundScapes;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


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

    /**
     * Stream audio dari Google Drive melalui proxy
     * GET /api/stream/{id}
     */
    public function streamAudio(int $id)
    {
        $soundscape = SoundScapes::find($id);
        
        if (!$soundscape) {
            abort(404, 'Soundscape not found');
        }

        if (empty($soundscape->audio_url)) {
            abort(400, 'Audio URL is empty');
        }

        $fileId = $this->extractFileId($soundscape->audio_url);
        
        if (!$fileId) {
            abort(400, 'Invalid Google Drive URL format');
        }

        $googleUrl = "https://drive.google.com/uc?export=download&id={$fileId}";

        // Jadikan Laravel sebagai Proxy sejati, bukan sekadar Redirect
        return response()->stream(function () use ($googleUrl) {
            // Buka koneksi ke Google Drive
            $stream = @fopen($googleUrl, 'rb');
            
            if ($stream) {
                // Alirkan data biner mentah langsung ke frontend
                fpassthru($stream);
                fclose($stream);
            } else {
                echo "Failed to load audio stream.";
            }
        }, 200, [
            'Content-Type'                => 'audio/mpeg', // Asumsi file Anda mp3
            'Cache-Control'               => 'no-cache, no-store, must-revalidate',
            'Access-Control-Allow-Origin' => '*', // Izinkan frontend Anda membaca ini
            'Accept-Ranges'               => 'none', // Matikan range request untuk proxy sederhana
        ]);
    }

    /**
     * Ekstrak File ID dari URL Google Drive
     */
   private function extractFileId(string $url): ?string
    {
        // 1. Cek apakah input sudah berupa ID murni yang valid (25-40 karakter)
        if (preg_match('/^[a-zA-Z0-9_-]{25,40}$/', $url)) {
            return $url;
        }

        if (preg_match('/(?:id=|\/d\/)([a-zA-Z0-9_-]{25,40})/', $url, $matches)) {
            return $matches[1];
        }

        return null; // Format tidak dikenali atau panjang ID tidak valid
    }
}