<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('festival_presets', function (Blueprint $table) {
            $table->id();
            $table->string('category', 20);
            $table->string('name', 100);
            $table->unsignedTinyInteger('month');
            $table->unsignedTinyInteger('day');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'month', 'day']);
            $table->unique(['category', 'name', 'month', 'day'], 'festival_presets_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('festival_presets');
    }
};
