<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SoundScapeResource;
use App\Models\SoundScapes;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;


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
            'data'    => SoundScapeResource::collection($soundscapes)
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
    public function streamAudio(Request $request, int $id)
    {
        // 1. Ambil token dari Authorization header atau query parameter 'token'
        $tokenString = $request->bearerToken() ?: $request->query('token');

        if (!$tokenString) {
            abort(401, 'Unauthorized: Token tidak ditemukan');
        }

        // 2. Validasi token Sanctum
        $token = PersonalAccessToken::findToken($tokenString);
        if (!$token || ($token->expires_at && $token->expires_at->isPast())) {
            abort(401, 'Unauthorized: Token tidak valid atau kedaluwarsa');
        }

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

        $requestHeaders = [];
        if ($request->hasHeader('Range')) {
            $requestHeaders[] = 'Range: ' . $request->header('Range');
        }

        $context = stream_context_create([
            'http' => [
                'header' => $requestHeaders,
                'follow_location' => true
            ]
        ]);

        $stream = @fopen($googleUrl, 'rb', false, $context);
        if (!$stream) {
            abort(500, 'Failed to open stream');
        }

        $meta = stream_get_meta_data($stream);
        $responseHeaders = $meta['wrapper_data'] ?? [];

        $statusCode = 200;
        $contentType = 'audio/mpeg';
        $contentLength = null;
        $contentRange = null;

        foreach ($responseHeaders as $headerLine) {
            if (preg_match('/^HTTP\/\d+\.\d+\s+(\d+)/i', $headerLine, $matches)) {
                $statusCode = (int)$matches[1];
            } elseif (preg_match('/^Content-Type:\s*(.+)/i', $headerLine, $matches)) {
                $contentType = trim($matches[1]);
            } elseif (preg_match('/^Content-Length:\s*(\d+)/i', $headerLine, $matches)) {
                $contentLength = (int)trim($matches[1]);
            } elseif (preg_match('/^Content-Range:\s*(.+)/i', $headerLine, $matches)) {
                $contentRange = trim($matches[1]);
            }
        }

        $headersToSend = [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Access-Control-Allow-Origin' => '*',
            'Accept-Ranges' => 'bytes',
        ];

        if ($contentLength !== null) {
            $headersToSend['Content-Length'] = $contentLength;
        }
        if ($contentRange !== null) {
            $headersToSend['Content-Range'] = $contentRange;
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, $statusCode, $headersToSend);
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