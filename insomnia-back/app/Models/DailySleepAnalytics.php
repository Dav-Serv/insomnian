<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailySleepAnalytics extends Model
{
    protected $fillable = [
        'user_id',
        'sleep_log_id',
        'sleep_score',
        'recovery_status',
        'recovery_message',
        'deep_sleep_minus',
        'restfulness_status',
        'calculated_date'
    ];
}
