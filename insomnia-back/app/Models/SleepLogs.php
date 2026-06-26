<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SleepLogs extends Model
{
    protected $fillable = [
        'user_id',
        'log_date',
        'bed_time',
        'wake_time',
        'sleep_quality_rating',
        'notes',
        'total_sleep_minutes'
    ];
}
