<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('festival_preset_id')->nullable()->constrained('festival_presets')->nullOnDelete();
            $table->date('date');
            $table->string('content', 255);
            $table->unsignedSmallInteger('remind_before_days')->default(0);
            $table->string('channel', 20)->default('email');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->unique(['user_id', 'date', 'content'], 'reminders_user_date_content_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
