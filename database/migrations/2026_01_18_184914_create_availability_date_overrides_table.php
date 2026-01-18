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
        Schema::create('availability_date_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_off')->default(false);
            $table->timestamps();

            $table->unique(['availability_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_date_overrides');
    }
};
