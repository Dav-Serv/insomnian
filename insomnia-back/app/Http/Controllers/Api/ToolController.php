<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    /**
     * Menampilkan halaman utama Tools (Rekomendasi Utama & Kategori Selengkapnya)
     * Sesuai dengan pembagian section pada Desain UI
     */
    public function index()
    {
        // 1. Section Atas: Rekomendasi Utama (Banner Besar Berdurasi Panjang)
        $recommendation = [
            'id'               => 2,
            'title'            => 'Relaksasi Otot Progresif',
            'subtitle'         => 'Melepaskan Ketegangan Fisik',
            'description'      => 'Teknik memicu respons rileks alami tubuh dengan meregangkan dan melepas ketegangan otot secara teratur dari ujung kepala hingga kaki.',
            'duration_minutes' => 30,
            'category_name'    => 'Meditasi',
            'thumbnail_url'    => asset('images/tools/muscle-relaxation-banner.png'),
            'action_type'      => 'audio_player', // Memandu user menggunakan audio panduan khusus
        ];

        // 2. Section Bawah: Kategori Selengkapnya (Grid horizontal/vertikal)
        $categories = [
            [
                'id'            => 1,
                'name'          => 'Pernapasan',
                'slug'          => 'pernapasan',
                'total_items'   => 3,
                'thumbnail_url' => asset('images/tools/cat-breathing.png'),
            ],
            [
                'id'            => 2,
                'name'          => 'Meditasi & Relaksasi',
                'slug'          => 'meditasi',
                'total_items'   => 4,
                'thumbnail_url' => asset('images/tools/cat-meditation.png'),
            ],
            [
                'id'            => 3,
                'name'          => 'Edukasi & Sleep Hygiene',
                'slug'          => 'edukasi',
                'total_items'   => 5,
                'thumbnail_url' => asset('images/tools/cat-education.png'),
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data halaman utama Tools berhasil dimuat',
            'data'    => [
                'recommendation' => $recommendation,
                'categories'     => $categories
            ]
        ]);
    }

    /**
     * Mengambil daftar item berdasarkan kategori tertentu (misal diklik kategori 'Pernapasan')
     */
    public function getByCategory($slug)
    {
        // Simulasi database item per kategori
        $items = [];

        if ($slug === 'pernapasan') {
            $items = [
                [
                    'id'               => 1,
                    'title'            => 'Latihan Pernapasan 4-7-8',
                    'description'      => 'Metode pernapasan dalam untuk menenangkan sistem saraf dengan cepat.',
                    'duration_minutes' => 5,
                    'thumbnail_url'    => asset('images/tools/breathing-478.png'),
                    'action_type'      => 'breathing_timer'
                ],
                [
                    'id'               => 4,
                    'title'            => 'Box Breathing (Pernapasan Kotak)',
                    'description'      => 'Teknik yang digunakan para profesional untuk membersihkan pikiran dan meredakan kecemasan.',
                    'duration_minutes' => 4,
                    'thumbnail_url'    => asset('images/tools/box-breathing.png'),
                    'action_type'      => 'breathing_timer'
                ]
            ];
        } elseif ($slug === 'edukasi') {
            $items = [
                [
                    'id'               => 3,
                    'title'            => 'Panduan Kamar Ideal (Sleep Hygiene)',
                    'description'      => 'Tips mengatur pencahayaan, suhu, dan gadget sebelum tidur.',
                    'duration_minutes' => 3,
                    'thumbnail_url'    => asset('images/tools/sleep-hygiene.png'),
                    'action_type'      => 'article'
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'category_slug' => $slug,
            'data'    => $items
        ]);
    }

    /**
     * Menampilkan konten detail tools yang dinamis sesuai tipenya
     */
    public function show($id)
    {
        // DETAIL TOOL ID 1: Latihan Pernapasan (Tipe: breathing_timer)
        if ($id == 1) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'id'            => 1,
                    'title'         => 'Latihan Pernapasan 4-7-8',
                    'action_type'   => 'breathing_timer',
                    'content'       => [
                        'cycles' => 4, // Jumlah perulangan ideal
                        'intervals' => [
                            'inhale_seconds' => 4,
                            'hold_seconds'   => 7,
                            'exhale_seconds' => 8
                        ],
                        'instructions' => [
                            'Tarik napas melalui hidung secara perlahan selama 4 detik.',
                            'Tahan napas Anda dengan rileks selama 7 detik.',
                            'Hembuskan napas sepenuhnya melalui mulut hingga berbunyi "whoosh" selama 8 detik.',
                            'Ulangi siklus ini sampai timer selesai.'
                        ]
                    ]
                ]
            ]);
        }

        // DETAIL TOOL ID 2: Relaksasi Otot (Tipe: audio_player)
        if ($id == 2) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'id'            => 2,
                    'title'         => 'Relaksasi Otot Progresif',
                    'action_type'   => 'audio_player',
                    'content'       => [
                        'audio_url'    => asset('audio/tools/progressive-muscle-guided.mp3'),
                        'guide_author' => 'Dr. Sleep Specialist',
                        'chapters'     => [
                            ['time' => '00:00', 'title' => 'Pengenalan & Posisi Tubuh'],
                            ['time' => '05:00', 'title' => 'Relaksasi Area Wajah & Leher'],
                            ['time' => '15:00', 'title' => 'Relaksasi Area Punggung & Perut'],
                            ['time' => '25:00', 'title' => 'Pelepasan Ketegangan Total']
                        ]
                    ]
                ]
            ]);
        }

        // DETAIL TOOL ID 3: Edukasi Kamar Ideal (Tipe: article)
        if ($id == 3) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'id'            => 3,
                    'title'         => 'Panduan Kamar Ideal',
                    'action_type'   => 'article',
                    'content'       => [
                        'body' => "<h3>1. Temperatur Kamar yang Sempurna</h3><p>Atur suhu kamar Anda pada kisaran 20-22 derajat Celcius...</p><h3>2. Redupkan Cahaya</h3><p>Gunakan lampu tidur berwarna warm light dan matikan layar HP minimal 30 menit sebelum tidur...</p>",
                        'reading_time' => '3 min read'
                    ]
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Detail instruksi tools tidak ditemukan'
        ], 404);
    }
}
