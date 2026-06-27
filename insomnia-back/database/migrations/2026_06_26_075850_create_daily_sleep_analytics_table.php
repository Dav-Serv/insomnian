<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_sleep_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('sleep_log_id')->references('id')->on('sleep_logs')->onDelete('cascade');
            $table->integer('sleep_score');
            $table->string('recovery_status');
            $table->text('recovery_message');
            $table->integer('deep_sleep_minutes');
            $table->string('restfulness_status');
            $table->date('calculated_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_sleep_analytics');
    }
};
