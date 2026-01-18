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
        Schema::create('availability_week_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_day_id')->constrained('availability_week_days')->cascadeOnDelete();
            $table->string('start_time', 5); // HH:MM
            $table->string('end_time', 5);   // HH:MM
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_week_ranges');
    }
};
