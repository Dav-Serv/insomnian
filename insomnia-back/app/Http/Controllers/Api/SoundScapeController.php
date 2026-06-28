<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SoundscapeResource;
use App\Models\SoundScapes;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

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
        // 1. Cari soundscape di database
        $soundscape = SoundScapes::find($id);
        
        if (!$soundscape) {
            abort(404, 'Soundscape not found');
        }

        // 2. Ekstrak File ID dari URL
        $fileId = $this->extractFileId($soundscape->audio_url);
        
        if (!$fileId) {
            abort(400, 'Invalid audio URL');
        }

        // 3. Bangun URL Google Drive yang benar
        $googleUrl = "https://drive.google.com/uc?export=open&id={$fileId}";

        // 4. Ambil file dari Google Drive
        try {
            $response = Http::withOptions([
                'stream' => true,
                'timeout' => 30,
            ])->get($googleUrl);
        } catch (\Exception $e) {
            abort(500, 'Failed to fetch audio from Google Drive');
        }

        // 5. Cek apakah response berhasil
        if ($response->failed()) {
            abort(500, 'Google Drive returned error');
        }

        // 6. Dapatkan konten dan MIME type
        $content = $response->body();
        $contentType = $response->header('Content-Type') ?? 'audio/mpeg';

        // 7. Kembalikan sebagai stream audio
        return response()->stream(
            function () use ($content) {
                echo $content;
            },
            200,
            [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'inline; filename="audio.mp3"',
                'Cache-Control' => 'public, max-age=86400', // cache 1 hari
                'Accept-Ranges' => 'bytes',
            ]
        );
    }

    /**
     * Ekstrak File ID dari URL Google Drive
     */
    private function extractFileId(string $url)
    {
        // Cocokkan pola: .../file/d/FILE_ID/... atau ...?id=FILE_ID
        preg_match('/\/file\/d\/([^\/]+)/', $url, $matches);
        
        if (isset($matches[1])) {
            return $matches[1];
        }

        // Alternatif: cari ?id=FILE_ID
        preg_match('/[?&]id=([^&]+)/', $url, $matches);
        
        return $matches[1] ?? null;
    }
}