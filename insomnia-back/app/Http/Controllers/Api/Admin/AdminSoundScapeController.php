<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SoundScapes;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminSoundScapeController extends Controller
{
    // CREATE: Admin menambah soundscape baru ke katalog
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'artist_name'      => 'required|string|max:255',
            'description'      => 'required|string',
            'category'         => 'required|string',
            'duration_minutes' => 'required|numeric',
            'thumbnail_url'    => 'required|url',
            'audio_url'        => 'required|url',
        ]);

        $soundscape = SoundScapes::create($validated);

        return response()->json([
            'success' => true, 
            'message' => 'Soundscape berhasil ditambahkan', 
            'data' => $soundscape
        ], 201);
    }

    // UPDATE: Admin merevisi metadata atau link URL
    public function update(Request $request, int $id): JsonResponse
    {
        $soundscape = SoundScapes::find($id);

        if (!$soundscape) return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);

        $validated = $request->validate([
            'title'            => 'sometimes|string|max:255',
            'artist_name'      => 'sometimes|string|max:255',
            'description'      => 'sometimes|string',
            'category'         => 'sometimes|string',
            'duration_minutes' => 'sometimes|numeric',
            'thumbnail_url'    => 'sometimes|url',
            'audio_url'        => 'sometimes|url',
        ]);

        $soundscape->update($validated);

        return response()->json(['success' => true, 'message' => 'Soundscape berhasil diupdate', 'data' => $soundscape]);
    }

    // DELETE: Admin menghapus audio dari sistem
    public function destroy(int $id): JsonResponse
    {
        $soundscape = SoundScapes::find($id);

        if (!$soundscape) return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);

        $soundscape->delete();

        return response()->json(['success' => true, 'message' => 'Soundscape berhasil dihapus']);
    }
    // READ: Menampilkan semua soundscape (dengan Pagination untuk Admin)
    public function index(): JsonResponse
    {
        // Gunakan pagination agar Postman/Frontend tidak hang saat data mencapai ribuan
        $soundscapes = SoundScapes::orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Data katalog berhasil dimuat',
            'data'    => $soundscapes
        ]);
    }

    // READ: Menampilkan detail spesifik satu soundscape berdasarkan ID
    public function show(int $id): JsonResponse
    {
        $soundscape = SoundScapes::find($id);

        if (!$soundscape) {
            return response()->json([
                'success' => false,
                'message' => 'Soundscape tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail soundscape berhasil dimuat',
            'data'    => $soundscape
        ]);
    }
}