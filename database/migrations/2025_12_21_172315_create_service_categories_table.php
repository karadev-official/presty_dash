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
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_profile_id')->constrained()->cascadeOnDelete(); // ✅ pro owner
            $table->string('name');
            $table->string('slug');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->nullable()->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->string('agenda_color')->nullable()->default("#c1cddf");
            $table->timestamps();

            // ✅ slug unique par pro (pas global)
            $table->unique(['professional_profile_id', 'slug']);
            $table->index(['professional_profile_id', 'is_active', 'is_online', 'position'], "professional_cards_index");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_categories');
    }
};
