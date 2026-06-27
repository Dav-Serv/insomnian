<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SleepLogs extends Model
{
    protected $table = 'sleep_logs';

    protected $fillable = [
        'user_id',
        'log_date',
        'bed_time',
        'wake_time',
        'sleep_quality_rating',
        'notes',
        'total_sleep_minutes'
    ];

    // Relasi balik ke User
    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Hubungan ke Analitik (Satu log menghasilkan satu analitik di Home)
    public function dailyAnalytic(): HasOne {
        return $this->hasOne(DailySleepAnalytics::class, 'sleep_log_id');
    }
}
