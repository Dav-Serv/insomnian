<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailySleepAnalytics;
use App\Models\SoundScapes;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Ambil analitik tidur terakhir milik user beserta data dari tabel sleep_logs
        $latestAnalytic = DailySleepAnalytics::with('sleepLog') // Eager load relasi sleepLog
            ->where('user_id', $user->id)
            ->orderBy('calculated_date', 'desc')
            ->first();

        // 2. Ambil 2 rekomendasi soundscape secara acak untuk bagian bawah UI
        $recommendations = SoundScapes::inRandomOrder()->take(2)->get();

        return response()->json([
            'success' => true,
            'message' => 'Data halaman Home berhasil dimuat',
            'data' => [
                'user' => [
                    'name' => $user->name, // Menyuplai "Selamat Pagi, Samael"
                ],
                'sleep_summary' => $latestAnalytic ? [
                    'sleep_score'        => $latestAnalytic->sleep_score,        // 85
                    'recovery_status'    => $latestAnalytic->recovery_status,    // "Excellent Recovery"
                    'recovery_message'   => $latestAnalytic->recovery_message,   // "Your resting heart rate..."
                    // Total menit diambil dari relasi sleepLog sesuai modelmu
                    'total_time'         => $this->formatMinutes($latestAnalytic->sleepLog->total_sleep_minutes ?? 0), // "7h 20m"
                    'deep_sleep'         => $this->formatMinutes($latestAnalytic->deep_sleep_minutes),  // "2h 15m"
                    'restfulness_status' => $latestAnalytic->restfulness_status, // "Optimal"
                ] : [
                    // Fallback jika user baru mendaftar dan belum punya data log tidur
                    'sleep_score'        => 0,
                    'recovery_status'    => 'No Data Yet',
                    'recovery_message'   => 'Mulai catat tidurmu malam ini di menu Diary untuk melihat analisis kesehatan tidurmu.',
                    'total_time'         => '0h 0m',
                    'deep_sleep'         => '0h 0m',
                    'restfulness_status' => 'Unknown',
                ],
                'recommended_soundscapes' => $recommendations
            ]
        ]);
    }

    // Mengubah menit mentah menjadi format teks "7h 20m" sesuai UI
    private function formatMinutes($minutes)
    {
        if ($minutes <= 0) return "0h 0m";
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        return "{$hours}h {$remainingMinutes}m";
    }
}
