<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySleepAnalytics extends Model
{
    protected $table = 'daily_sleep_analytics';

    protected $fillable = [
        'user_id',
        'sleep_log_id',
        'sleep_score',
        'recovery_status',
        'recovery_message',
        'deep_sleep_minutes',
        'restfulness_status',
        'calculated_date'
    ];

    // Relasi balik ke User
    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi balik ke Log Tidur asalnya
    public function sleepLog(): BelongsTo {
        return $this->belongsTo(SleepLogs::class, 'sleep_log_id');
    }
}
