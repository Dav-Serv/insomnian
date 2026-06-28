<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailySleepAnalytics;
use App\Models\SleepLogs;
use App\Models\SoundScapes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Ambil data analitik tidur terakhir
        $latestAnalytic = DailySleepAnalytics::with('sleepLog')
            ->where('user_id', $user->id)
            ->orderBy('calculated_date', 'desc')
            ->first();

        // 2. Ambil 2 rekomendasi soundscape
        $recommendations = SoundScapes::inRandomOrder()->take(2)->get()->map(function($item) {
            // Resolusi thumbnail dengan fallback CDN jika berkas gambar lokal tidak ditemukan
            $thumbnail = $item->thumbnail_url;
            if ($thumbnail && !filter_var($thumbnail, FILTER_VALIDATE_URL)) {
                if (!file_exists(public_path($thumbnail))) {
                    $category = strtolower($item->category);
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
                'id'            => $item->id,
                'title'         => $item->title,
                'artist_name'   => $item->artist_name,
                'category'      => $item->category,
                // Mengubah format tampilan menjadi "Nature • 45 min" sesuai UI
                'subtitle_ui'   => "{$item->category} • {$item->duration_minutes} min",
                'thumbnail_url' => $thumbnail,
                'audio_url'     => $item->audio_url ? (filter_var($item->audio_url, FILTER_VALIDATE_URL) ? $item->audio_url : asset($item->audio_url)) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data halaman Home berhasil dimuat',
            'data' => [
                'user' => [
                    'name'      => $user->name,
                    'photo'     => $user->photo ? asset('storage/' . $user->photo) : null
                ],
                'sleep_summary' => $latestAnalytic ? [
                    'sleep_score'        => "{$latestAnalytic->sleep_score}%", // Ditambah % sesuai UI
                    'recovery_status'    => $latestAnalytic->recovery_status,
                    'recovery_message'   => $latestAnalytic->recovery_message,
                    'total_time'         => $this->formatMinutes($latestAnalytic->sleepLog->total_sleep_minutes ?? 0), // "6h 45m"
                    'deep_sleep'         => $this->formatMinutes($latestAnalytic->deep_sleep_minutes),  // "1h 20m"
                    'restfulness_status' => $latestAnalytic->restfulness_status,
                ] : [
                    'sleep_score'        => '0%',
                    'recovery_status'    => 'No Data Yet',
                    'recovery_message'   => 'Mulai catat tidurmu malam ini untuk melihat analisis kesehatan tidurmu.',
                    'total_time'         => '0h 0m',
                    'deep_sleep'         => '0h 0m',
                    'restfulness_status' => 'Unknown',
                ],
                'recommended_soundscapes' => $recommendations
            ]
        ]);
    }

    private function formatMinutes($minutes)
    {
        if ($minutes <= 0) return "0h 0m";
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        return "{$hours}h {$remainingMinutes}m";
    }

    // mockSleepLog hanya untuk testing, kalau mau deploy di comment saja codenya
    public function mockSleepLog(Request $request)
    {
        $user = $request->user();

        DB::beginTransaction();
        try {
            // 1. Log Tidur (Dilengkapi semua kolom sesuai file migration asli)
            $sleepLog = SleepLogs::create([
                'user_id'              => $user->id,
                'log_date'             => now()->toDateString(),
                'bed_time'             => now()->subHours(8)->format('Y-m-d H:i:s'), // Simulasi jam tidur 8 jam lalu
                'wake_time'            => now()->format('Y-m-d H:i:s'),            // Simulasi jam bangun sekarang
                'sleep_quality_rating' => 8,                                        // Skala 1-10 (Tidur Nyenyak)
                'notes'                => 'Tidur sangat nyenyak setelah mendengarkan Soundscape hujan.', //
                'total_sleep_minutes'  => 440,                                      // 7 Jam 20 Menit sesuai UI
            ]);

            // 2. Analisis Tidur (Sesuai dengan database daily_sleep_analytics)
            DailySleepAnalytics::create([
                'user_id'            => $user->id,
                'sleep_log_id'       => $sleepLog->id,
                'sleep_score'        => 85, // Nilai 85% di UI
                'recovery_status'    => 'Excellent Recovery', // Judul di UI
                'recovery_message'   => 'Detak jantung istirahat Anda stabil lebih awal tadi malam, memberikan tubuh Anda waktu optimal untuk memulihkan tenaga.', // Deskripsi di UI
                'deep_sleep_minutes' => 135, // 2 Jam 15 Menit di UI
                'restfulness_status' => 'Optimal', // Indikator Ketenangan di UI
                'calculated_date'    => now()->toDateString(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Simulasi data tidur harian berhasil dibuat! Silakan cek kembali endpoint GET /api/home.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat simulasi data',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
