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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pro_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('name');
            $table->string('specialty')->nullable();

            $table->string('type');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('resource_image_id')->nullable()->constrained('images')->nullOnDelete();
            $table->foreignId('linked_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['pro_user_id', 'type']);
            $table->index(['pro_user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
