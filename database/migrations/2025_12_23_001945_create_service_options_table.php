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
        Schema::create('service_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_option_group_id')
                ->constrained('service_option_groups')
                ->cascadeOnDelete();

            $table->string('name'); // "Court", "Moyen", "Extra 1"
            $table->integer('price')->default(0);     // centimes (peut être 0)
            $table->integer('duration')->default(0);  // minutes (peut être 0)

            $table->string('image_url')->nullable(); // ou image_path plus tard
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('position')->default(0);

            $table->timestamps();

            $table->index(['service_option_group_id', 'is_active', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_options');
    }
};
