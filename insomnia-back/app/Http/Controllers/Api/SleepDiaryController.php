<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailySleepAnalytics;
use App\Models\SleepLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SleepDiaryController extends Controller
{
    public function index(Request $request){
        $user = $request->user();

        // Mengambil riwayat tidur beserta hasil analisisnya
        $diaries = SleepLogs::with('dailyAnalytic')
            ->where('user_id', $user->id)
            ->orderBy('log_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat Sleep Diary berhasil dimuat',
            'data'    => $diaries
        ]);
    }

    public function store(Request $request){
        $user = $request->user();

        // 1. Validasi Input dari Frontend
        $request->validate([
            'log_date'             => 'required|date',
            'bed_time'             => 'required|date_format:Y-m-d H:i:s',
            'wake_time'            => 'required|date_format:Y-m-d H:i:s|after:bed_time',
            'sleep_quality_rating' => 'required|integer|min:1|max:10', // Skala 1-10
            'notes'                => 'nullable|string',
        ]);

        // 2. Hitung total durasi tidur dalam menit menggunakan Carbon
        $bedTime = Carbon::parse($request->bed_time);
        $wakeTime = Carbon::parse($request->wake_time);
        $totalSleepMinutes = $bedTime->diffInMinutes($wakeTime);

        DB::beginTransaction();
        try {
            // 3. Simpan data ke tabel sleep_logs
            $sleepLog = SleepLogs::create([
                'user_id'              => $user->id,
                'log_date'             => $request->log_date,
                'bed_time'             => $request->bed_time,
                'wake_time'            => $request->wake_time,
                'sleep_quality_rating' => $request->sleep_quality_rating,
                'notes'                => $request->notes ?? '-',
                'total_sleep_minutes'  => $totalSleepMinutes,
            ]);

            // 4. Algoritma Hitung Analisis Tidur Otomatis (Dinamis)
            // Asumsi waktu tidur ideal adalah 8 jam (480 menit)
            $durationScore = min(70, round(($totalSleepMinutes / 480) * 70)); 
            $qualityScore = $request->sleep_quality_rating * 3; // Max 30 poin dari kualitas tidur
            $sleepScore = min(100, $durationScore + $qualityScore); // Total max 100%

            // Tentukan status recovery & restfulness berdasarkan skor hasil kalkulasi
            if ($sleepScore >= 85) {
                $recoveryStatus = 'Excellent Recovery';
                $restfulnessStatus = 'Optimal';
                $recoveryMessage = 'Detak jantung istirahat Anda stabil lebih awal tadi malam, memberikan tubuh Anda waktu optimal untuk memulihkan tenaga.';
            } elseif ($sleepScore >= 70) {
                $recoveryStatus = 'Good Recovery';
                $restfulnessStatus = 'Sufficient';
                $recoveryMessage = 'Tidur Anda cukup baik. Tubuh Anda berhasil memulihkan sebagian besar energi untuk aktivitas hari ini.';
            } else {
                $recoveryStatus = 'Poor Recovery';
                $restfulnessStatus = 'Inadequate';
                $recoveryMessage = 'Durasi atau kualitas tidur Anda kurang optimal. Cobalah beristirahat lebih awal malam ini.';
            }

            // Estimasi deep sleep adalah kisaran 20% - 25% dari total waktu tidur
            $deepSleepMinutes = round($totalSleepMinutes * 0.22);

            // 5. Simpan hasil kalkulasi ke tabel daily_sleep_analytics
            $analytics = DailySleepAnalytics::create([
                'user_id'            => $user->id,
                'sleep_log_id'       => $sleepLog->id,
                'sleep_score'        => $sleepScore,
                'recovery_status'    => $recoveryStatus,
                'recovery_message'   => $recoveryMessage,
                'deep_sleep_minutes' => $deepSleepMinutes,
                'restfulness_status' => $restfulnessStatus,
                'calculated_date'    => $request->log_date,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Catatan tidur dan hasil analisis berhasil disimpan!',
                'data' => [
                    'sleep_log' => $sleepLog,
                    'analytics' => $analytics
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan catatan diary',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
