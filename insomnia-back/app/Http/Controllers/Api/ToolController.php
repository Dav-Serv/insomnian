<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function index()
    {
        // 1. Bagian Rekomendasi Utama (Banner Besar Atas)
        $recommendation = [
            'id'               => 2,
            'title'            => 'Relaksasi Otot Progresif',
            'subtitle'         => 'Melepaskan Ketegangan Fisik',
            'description'      => 'Teknik memicu respons rileks alami tubuh dengan meregangkan dan melepas ketegangan otot secara teratur.',
            'duration_minutes' => 30,
            'thumbnail_url'    => asset('images/tools/muscle-banner.png'),
        ];

        // 2. Bagian Kategori Selengkapnya (Grid Bawah)
        $categories = [
            [
                'id'            => 1,
                'name'          => 'Pernapasan',
                'total_items'   => 4,
                'thumbnail_url' => asset('images/tools/cat-breathing.png'),
            ],
            [
                'id'            => 2,
                'name'          => 'Meditasi',
                'total_items'   => 6,
                'thumbnail_url' => asset('images/tools/cat-meditation.png'),
            ],
            [
                'id'            => 3,
                'name'          => 'Edukasi',
                'total_items'   => 3,
                'thumbnail_url' => asset('images/tools/cat-education.png'),
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Halaman Tools berhasil dimuat',
            'data'    => [
                'recommendation' => $recommendation,
                'categories'     => $categories
            ]
        ]);
    }

    public function show($id)
    {
        if ($id == 1 || $id == 2) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'title' => 'Latihan Pernapasan 4-7-8',
                    'steps' => [
                        'Tarik napas melalui hidung secara perlahan selama 4 detik.',
                        'Tahan napas Anda dengan rileks selama 7 detik.',
                        'Hembuskan napas sepenuhnya melalui mulut selama 8 detik.',
                        'Ulangi siklus ini sebanyak 4 kali.'
                    ]
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Detail tools tidak ditemukan'
        ], 404);
    }
}
