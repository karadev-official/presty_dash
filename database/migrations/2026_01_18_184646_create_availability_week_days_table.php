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
        Schema::create('availability_week_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 1..7
            $table->boolean('enabled')->default(false);
            $table->unsignedSmallInteger('slot_duration_min')->default(30);
            $table->timestamps();

            $table->unique(['availability_id', 'weekday']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_week_days');
    }
};
